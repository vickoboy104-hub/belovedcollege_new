<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentProvider;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OPayGateway implements PaymentGateway
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::OPay;
    }

    public function isConfigured(): bool
    {
        return filled(Setting::getValue('opay_merchant_id'))
            && filled(Setting::getValue('opay_public_key'))
            && filled(Setting::getValue('opay_secret_key'));
    }

    public function initialize(object $invoice, Payment $payment): array
    {
        $merchantId = (string) Setting::getValue('opay_merchant_id');
        $publicKey = (string) Setting::getValue('opay_public_key');

        if (! $this->isConfigured() || $merchantId === '' || $publicKey === '') {
            throw new RuntimeException('OPay merchant credentials are not completely configured.');
        }

        $student = $invoice->student;
        $callbackUrl = route('payments.callback', [
            'provider' => 'opay',
            'reference' => $payment->reference,
        ]);
        $webhookUrl = route('webhooks.opay', ['reference' => $payment->reference]);

        $payload = [
            'country' => 'NG',
            'reference' => $payment->reference,
            'amount' => [
                'total' => (int) round(((float) $payment->amount) * 100),
                'currency' => strtoupper((string) $payment->currency),
            ],
            'returnUrl' => $callbackUrl,
            'callbackUrl' => $webhookUrl,
            'cancelUrl' => route('portal.index', ['section' => 'billing']),
            'product' => [
                'name' => 'School fees payment',
                'description' => 'Payment for '.($invoice->invoice_no ?? 'selected school bills'),
            ],
            'userInfo' => [
                'userEmail' => (string) ($student?->user?->email ?? ''),
                'userName' => (string) ($student?->user?->name ?? ''),
            ],
        ];

        $preferredMethod = trim((string) Setting::getValue('opay_pay_method', ''));
        if ($preferredMethod !== '') {
            $payload['payMethod'] = $preferredMethod;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$publicKey,
            'MerchantId' => $merchantId,
        ])
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 300)
            ->post($this->baseUrl().'/api/v1/international/cashier/create', $payload);

        $body = $response->json();
        $cashierUrl = (string) (data_get($body, 'data.cashierUrl') ?: data_get($body, 'cashierUrl'));

        if ($response->failed() || $cashierUrl === '') {
            throw new RuntimeException((string) (data_get($body, 'message') ?: 'Unable to initialize OPay checkout.'));
        }

        return [
            'status' => true,
            'message' => data_get($body, 'message'),
            'data' => [
                'authorization_url' => $cashierUrl,
                'gateway_reference' => data_get($body, 'data.orderNo') ?: data_get($body, 'orderNo'),
                'reference' => $payment->reference,
            ],
            'raw' => $body,
        ];
    }

    public function verify(string $reference, array $context = []): array
    {
        $merchantId = (string) Setting::getValue('opay_merchant_id');
        $secretKey = (string) Setting::getValue('opay_secret_key');

        if (! $this->isConfigured() || $merchantId === '' || $secretKey === '') {
            throw new RuntimeException('OPay merchant credentials are not completely configured.');
        }

        $payload = [
            'reference' => $reference,
            'country' => 'NG',
        ];
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Unable to prepare OPay verification request.');
        }

        $signature = hash_hmac('sha512', $json, $secretKey);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$signature,
            'MerchantId' => $merchantId,
            'Content-Type' => 'application/json',
        ])
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 300)
            ->withBody($json, 'application/json')
            ->post($this->baseUrl().'/api/v1/international/cashier/status');

        $body = $response->json();
        $data = data_get($body, 'data', []);

        if ($response->failed() || ! is_array($data)) {
            throw new RuntimeException((string) (data_get($body, 'message') ?: 'Unable to verify OPay payment.'));
        }

        return [
            'status' => true,
            'message' => data_get($body, 'message'),
            'data' => [
                'status' => strtolower((string) data_get($data, 'status')),
                'reference' => (string) (data_get($data, 'reference') ?: $reference),
                'amount' => (int) data_get($data, 'amount.total', -1),
                'currency' => strtoupper((string) data_get($data, 'amount.currency')),
                'gateway_reference' => data_get($data, 'orderNo'),
                'channel' => 'opay',
                'paid_at' => data_get($data, 'createTime') ?: data_get($data, 'updateTime'),
            ],
            'raw' => $body,
        ];
    }

    protected function baseUrl(): string
    {
        return Setting::getValue('opay_environment', 'sandbox') === 'live'
            ? 'https://liveapi.opaycheckout.com'
            : 'https://testapi.opaycheckout.com';
    }
}
