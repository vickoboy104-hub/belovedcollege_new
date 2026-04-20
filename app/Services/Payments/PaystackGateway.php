<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway
{
    public function initialize(object $invoice, Payment $payment): array
    {
        $secret = Setting::getValue('paystack_secret_key');

        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $invoice->student->user->email,
                'amount' => (int) round(((float) $payment->amount) * 100),
                'currency' => 'NGN',
                'reference' => $payment->reference,
                'callback_url' => route('payments.callback', 'paystack'),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id ?? null,
                    'invoice_ids' => data_get($payment->payload, 'invoice_ids', []),
                    'student_id' => $invoice->student_id,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'Unable to initialize Paystack payment.');
        }

        return $response->json();
    }

    public function verify(string $reference): array
    {
        $secret = Setting::getValue('paystack_secret_key');

        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'Unable to verify Paystack payment.');
        }

        return $response->json();
    }
}
