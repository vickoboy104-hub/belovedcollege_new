<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function paystack(Request $request): JsonResponse
    {
        $secret = Setting::getValue('paystack_webhook_secret') ?: Setting::getValue('paystack_secret_key');
        $signature = (string) $request->header('x-paystack-signature');

        if (! $secret || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($request->input('event') !== 'charge.success') {
            return response()->json(['received' => true]);
        }

        $reference = (string) $request->input('data.reference');
        $payment = Payment::with('feeInvoice')
            ->where('reference', $reference)
            ->where('provider', PaymentProvider::Paystack->value)
            ->first();

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

        if ($payment->status !== PaymentStatus::Paid) {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'gateway_reference' => $reference,
                'channel' => $request->input('data.channel'),
                'paid_at' => now(),
                'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'payload' => array_merge($payment->payload ?? [], ['gateway' => $request->input('data', [])]),
            ]);

            $payment->feeInvoice?->syncBalance();
            if (! $payment->feeInvoice) {
                $payment->allocateBundleInvoices();
            }
        }

        return response()->json(['received' => true]);
    }

    public function palmpay(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'PalmPay webhook verification is not configured. No payment was updated.',
        ], 503);
    }
}
