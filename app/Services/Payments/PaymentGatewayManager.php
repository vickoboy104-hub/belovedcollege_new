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
    ) {
        $this->gateways = collect([$paystack, $palmPay, $flutterwave, $monnify])
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
        $raw = Setting::getValue('enabled_payment_gateways', 'paystack,palmpay');
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

                return [
                    'value' => $provider->value,
                    'label' => $provider->label(),
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
}
