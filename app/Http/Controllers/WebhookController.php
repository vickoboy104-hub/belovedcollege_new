<?php

namespace App\Http\Controllers;

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

        if ($request->input('event') === 'charge.success') {
            $payment = Payment::with('feeInvoice')->where('reference', $request->input('data.reference'))->first();

            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'gateway_reference' => $request->input('data.reference'),
                    'channel' => $request->input('data.channel'),
                    'paid_at' => now(),
                    'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                    'payload' => array_merge($payment->payload ?? [], $request->all()),
                ]);

                $payment->feeInvoice?->syncBalance();
                if (! $payment->feeInvoice) {
                    $payment->allocateBundleInvoices();
                }
            }
        }

        return response()->json(['received' => true]);
    }

    public function palmpay(Request $request): JsonResponse
    {
        $reference = (string) ($request->input('reference') ?? $request->input('orderNo') ?? data_get($request->input('data'), 'reference'));
        $status = strtolower((string) ($request->input('status') ?? data_get($request->input('data'), 'status')));

        if ($reference && in_array($status, ['success', 'paid', 'completed'], true)) {
            $payment = Payment::with('feeInvoice')->where('reference', $reference)->first();

            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'gateway_reference' => $request->input('transactionId') ?? data_get($request->input('data'), 'transactionId'),
                    'paid_at' => now(),
                    'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                    'payload' => array_merge($payment->payload ?? [], $request->all()),
                ]);

                $payment->feeInvoice?->syncBalance();
                if (! $payment->feeInvoice) {
                    $payment->allocateBundleInvoices();
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
