<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentProvider;
use App\Models\Setting;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGateway> */
    protected array $gateways;

    public function __construct(
        PaystackGateway $paystack,
        PalmPayGateway $palmPay,
        FlutterwaveGateway $flutterwave,
        MonnifyGateway $monnify,
        OPayGateway $opay,
    ) {
        $this->gateways = collect([$paystack, $palmPay, $flutterwave, $monnify, $opay])
            ->mapWithKeys(fn (PaymentGateway $gateway) => [$gateway->provider()->value => $gateway])
            ->all();
    }

    public function gateway(PaymentProvider|string $provider): PaymentGateway
    {
        $value = $provider instanceof PaymentProvider ? $provider->value : $provider;
        $gateway = $this->gateways[$value] ?? null;

        if (! $gateway) {
            throw new InvalidArgumentException('Unsupported online payment gateway: '.$value);
        }

        return $gateway;
    }

    public function isEnabled(PaymentProvider|string $provider): bool
    {
        $value = $provider instanceof PaymentProvider ? $provider->value : $provider;

        return in_array($value, $this->enabledValues(), true);
    }

    public function isAvailable(PaymentProvider|string $provider): bool
    {
        $value = $provider instanceof PaymentProvider ? $provider->value : $provider;

        return $this->isEnabled($value) && $this->gateway($value)->isConfigured();
    }

    /** @return array<int, string> */
    public function enabledValues(): array
    {
        $raw = Setting::getValue('enabled_payment_gateways', 'paystack');
        $values = is_array($raw) ? $raw : explode(',', (string) $raw);

        return collect($values)
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => array_key_exists($value, $this->gateways))
            ->unique()
            ->values()
            ->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function catalog(bool $onlyAvailable = false): Collection
    {
        return collect($this->gateways)
            ->map(function (PaymentGateway $gateway) {
                $provider = $gateway->provider();
                $meta = $this->metadata($provider);

                return [
                    'value' => $provider->value,
                    'label' => $provider->label(),
                    'initials' => $meta['initials'],
                    'description' => $meta['description'],
                    'recommended' => $meta['recommended'],
                    'mode' => $meta['mode'],
                    'enabled' => $this->isEnabled($provider),
                    'configured' => $gateway->isConfigured(),
                    'available' => $this->isAvailable($provider),
                    'callback_url' => route('payments.callback', $provider->value),
                    'webhook_url' => route('webhooks.'.$provider->value),
                ];
            })
            ->when($onlyAvailable, fn (Collection $items) => $items->where('available', true))
            ->values();
    }

    /** @return array{initials:string,description:string,recommended:bool,mode:string} */
    protected function metadata(PaymentProvider $provider): array
    {
        return match ($provider) {
            PaymentProvider::Paystack => [
                'initials' => 'PS',
                'description' => 'Cards, bank transfer, USSD and other Paystack checkout channels.',
                'recommended' => true,
                'mode' => $this->keyMode((string) Setting::getValue('paystack_public_key', ''), 'pk_test_'),
            ],
            PaymentProvider::Flutterwave => [
                'initials' => 'FW',
                'description' => 'Cards, bank transfer, USSD and supported wallet channels.',
                'recommended' => false,
                'mode' => $this->keyMode((string) Setting::getValue('flutterwave_public_key', ''), 'TEST'),
            ],
            PaymentProvider::Monnify => [
                'initials' => 'MN',
                'description' => 'Cards, account transfer and USSD through Monnify checkout.',
                'recommended' => false,
                'mode' => Setting::getValue('monnify_environment', 'sandbox') === 'live' ? 'Live mode' : 'Sandbox',
            ],
            PaymentProvider::OPay => [
                'initials' => 'OP',
                'description' => 'OPay Cashier hosted checkout with separate sandbox and live environments.',
                'recommended' => false,
                'mode' => Setting::getValue('opay_environment', 'sandbox') === 'live' ? 'Live mode' : 'Sandbox',
            ],
            PaymentProvider::PalmPay => [
                'initials' => 'PP',
                'description' => 'PalmPay merchant checkout template for schools with direct merchant onboarding.',
                'recommended' => false,
                'mode' => 'Merchant onboarding',
            ],
            default => [
                'initials' => 'PAY',
                'description' => 'Online payment gateway.',
                'recommended' => false,
                'mode' => 'Configured',
            ],
        };
    }

    protected function keyMode(string $key, string $testMarker): string
    {
        if (trim($key) === '') {
            return 'Not configured';
        }

        return str_contains(strtoupper($key), strtoupper($testMarker)) ? 'Test mode' : 'Live mode';
    }
}
