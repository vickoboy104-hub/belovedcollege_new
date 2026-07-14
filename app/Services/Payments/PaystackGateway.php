<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentProvider;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::Paystack;
    }

    public function isConfigured(): bool
    {
        return filled(Setting::getValue('paystack_secret_key'));
    }

    public function initialize(object $invoice, Payment $payment): array
    {
        $secret = Setting::getValue('paystack_secret_key');

        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 300)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $invoice->student->user->email,
                'amount' => (int) round(((float) $payment->amount) * 100),
                'currency' => $payment->currency,
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

    public function verify(string $reference, array $context = []): array
    {
        $secret = Setting::getValue('paystack_secret_key');

        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 300)
            ->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'Unable to verify Paystack payment.');
        }

        return $response->json();
    }
}
