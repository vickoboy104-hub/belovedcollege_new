<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Setting;
use RuntimeException;

class PalmPayGateway
{
    public function initialize(object $invoice, Payment $payment): array
    {
        $checkoutUrl = Setting::getValue('palmpay_checkout_url');

        if (! $checkoutUrl) {
            throw new RuntimeException('PalmPay checkout URL is not configured in admin settings.');
        }

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

    public function verify(string $reference): array
    {
        return [
            'status' => true,
            'data' => [
                'reference' => $reference,
                'gateway_response' => 'PalmPay verification is handled through the merchant callback/webhook payload.',
            ],
        ];
    }
}
