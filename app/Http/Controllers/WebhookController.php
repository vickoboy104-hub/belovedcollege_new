<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class WebhookController extends Controller
{
    public function __construct(protected PaymentGatewayManager $gateways)
    {
    }

    public function paystack(Request $request): JsonResponse
    {
        $secret = Setting::getValue('paystack_webhook_secret') ?: Setting::getValue('paystack_secret_key');
        $signature = (string) $request->header('x-paystack-signature');

        if (! $secret || ! $signature || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($request->input('event') !== 'charge.success') {
            return response()->json(['received' => true]);
        }

        $reference = (string) $request->input('data.reference');
        $payment = $this->payment($reference, PaymentProvider::Paystack);
        if (! $payment) {
            return response()->json(['message' => 'Payment reference not found'], 404);
        }

        $gatewayAmount = (int) $request->input('data.amount', -1);
        $expectedAmount = (int) round(((float) $payment->amount) * 100);
        $currency = strtoupper((string) $request->input('data.currency'));
        $status = strtolower((string) $request->input('data.status'));

        if ($status !== 'success' || $gatewayAmount !== $expectedAmount || $currency !== strtoupper((string) $payment->currency)) {
            return response()->json(['message' => 'Payment verification mismatch'], 422);
        }

        $this->settle($payment, [
            'gateway_reference' => $reference,
            'channel' => $request->input('data.channel'),
            'payload' => ['webhook' => $request->input('data', [])],
        ]);

        return response()->json(['received' => true]);
    }

    public function flutterwave(Request $request): JsonResponse
    {
        $secretHash = Setting::getValue('flutterwave_secret_hash');
        $signature = (string) $request->header('flutterwave-signature');
        $expected = $secretHash ? base64_encode(hash_hmac('sha256', $request->getContent(), $secretHash, true)) : null;

        if (! $expected || ! $signature || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $reference = (string) ($request->input('data.tx_ref') ?: $request->input('tx_ref'));
        $transactionId = $request->input('data.id') ?: $request->input('id');
        $payment = $this->payment($reference, PaymentProvider::Flutterwave);
        if (! $payment) {
            return response()->json(['message' => 'Payment reference not found'], 404);
        }

        try {
            $verification = $this->gateways->gateway(PaymentProvider::Flutterwave)->verify($reference, [
                'transaction_id' => $transactionId,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Payment verification is temporarily unavailable'], 503);
        }

        if (! $this->matches($payment, PaymentProvider::Flutterwave, $verification)) {
            return response()->json(['message' => 'Payment verification mismatch'], 422);
        }

        $this->settle($payment, [
            'gateway_reference' => data_get($verification, 'data.gateway_reference'),
            'channel' => data_get($verification, 'data.channel'),
            'payload' => ['webhook_verification' => $verification],
        ]);

        return response()->json(['received' => true]);
    }

    public function monnify(Request $request): JsonResponse
    {
        $secret = Setting::getValue('monnify_secret_key');
        $signature = (string) $request->header('monnify-signature');
        $expected = $secret ? hash_hmac('sha512', $request->getContent(), $secret) : null;

        if (! $expected || ! $signature || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $reference = (string) ($request->input('eventData.paymentReference') ?: $request->input('paymentReference'));
        $payment = $this->payment($reference, PaymentProvider::Monnify);
        if (! $payment) {
            return response()->json(['message' => 'Payment reference not found'], 404);
        }

        try {
            $verification = $this->gateways->gateway(PaymentProvider::Monnify)->verify($reference);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Payment verification is temporarily unavailable'], 503);
        }

        if (! $this->matches($payment, PaymentProvider::Monnify, $verification)) {
            return response()->json(['message' => 'Payment verification mismatch'], 422);
        }

        $this->settle($payment, [
            'gateway_reference' => data_get($verification, 'data.gateway_reference'),
            'channel' => data_get($verification, 'data.channel'),
            'paid_at' => data_get($verification, 'data.paid_at'),
            'payload' => ['webhook_verification' => $verification],
        ]);

        return response()->json(['received' => true]);
    }

    public function palmpay(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'PalmPay merchant-specific webhook verification is not configured. No payment was updated.',
        ], 503);
    }

    protected function payment(string $reference, PaymentProvider $provider): ?Payment
    {
        if ($reference === '') {
            return null;
        }

        return Payment::with('feeInvoice')
            ->where('reference', $reference)
            ->where('provider', $provider->value)
            ->first();
    }

    protected function matches(Payment $payment, PaymentProvider $provider, array $verification): bool
    {
        $status = strtolower((string) data_get($verification, 'data.status'));
        $reference = (string) data_get($verification, 'data.reference');
        $currency = strtoupper((string) data_get($verification, 'data.currency'));
        $amount = (float) data_get($verification, 'data.amount', -1);

        if (! hash_equals($payment->reference, $reference) || $currency !== strtoupper((string) $payment->currency)) {
            return false;
        }

        return match ($provider) {
            PaymentProvider::Flutterwave => in_array($status, ['successful', 'success'], true)
                && abs($amount - (float) $payment->amount) < 0.01,
            PaymentProvider::Monnify => in_array($status, ['paid', 'overpaid'], true)
                && $amount + 0.00001 >= (float) $payment->amount,
            default => false,
        };
    }

    protected function settle(Payment $payment, array $attributes): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Paid,
            'gateway_reference' => $attributes['gateway_reference'] ?? $payment->gateway_reference,
            'channel' => $attributes['channel'] ?? $payment->channel,
            'paid_at' => $attributes['paid_at'] ?? now(),
            'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'payload' => array_merge($payment->payload ?? [], $attributes['payload'] ?? []),
        ]);

        $payment->refresh();
        $payment->feeInvoice?->syncBalance();
        if (! $payment->feeInvoice) {
            $payment->allocateBundleInvoices();
        }
    }
}
