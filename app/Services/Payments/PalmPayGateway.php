<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentProvider;
use App\Models\Payment;
use App\Models\Setting;
use RuntimeException;

class PalmPayGateway implements PaymentGateway
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::PalmPay;
    }

    public function isConfigured(): bool
    {
        return filled(Setting::getValue('palmpay_checkout_url'))
            && filled(Setting::getValue('palmpay_merchant_id'))
            && filled(Setting::getValue('palmpay_private_key'));
    }

    public function initialize(object $invoice, Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PalmPay merchant checkout is not completely configured.');
        }

        $checkoutUrl = Setting::getValue('palmpay_checkout_url');
        $query = http_build_query([
            'reference' => $payment->reference,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'currency' => $payment->currency,
            'invoice_no' => $invoice->invoice_no ?? $payment->reference,
            'student_email' => $invoice->student->user->email,
            'student_name' => $invoice->student->user->name,
            'callback_url' => route('payments.callback', 'palmpay'),
        ]);

        return [
            'status' => true,
            'data' => [
                'authorization_url' => str($checkoutUrl)->contains('?')
                    ? "{$checkoutUrl}&{$query}"
                    : "{$checkoutUrl}?{$query}",
            ],
        ];
    }

    public function verify(string $reference, array $context = []): array
    {
        throw new RuntimeException(
            'PalmPay server-to-server verification requires the merchant-specific API contract supplied during PalmPay onboarding. No payment was marked as paid.'
        );
    }
}
