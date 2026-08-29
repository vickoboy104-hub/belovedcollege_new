<?php

namespace App\Services\Payments;

use App\Enums\PaymentProvider;
use Illuminate\Support\Collection;

class PaymentMethodResolver
{
    public function __construct(protected PaymentGatewayManager $gateways)
    {
    }

    public function providerFor(string $method): ?PaymentProvider
    {
        $preferences = match ($method) {
            'card' => [PaymentProvider::Paystack, PaymentProvider::Flutterwave, PaymentProvider::Monnify],
            'ussd' => [PaymentProvider::Paystack, PaymentProvider::Flutterwave, PaymentProvider::Monnify],
            'wallet' => [PaymentProvider::OPay, PaymentProvider::PalmPay],
            default => [],
        };

        foreach ($preferences as $provider) {
            if ($this->gateways->isAvailable($provider)) {
                return $provider;
            }
        }

        return null;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function catalog(bool $bankTransferAvailable): Collection
    {
        return collect([
            [
                'value' => 'card',
                'label' => 'Card Payment',
                'description' => 'Pay securely with your bank card.',
                'available' => $this->providerFor('card') !== null,
            ],
            [
                'value' => 'bank-transfer',
                'label' => 'Bank Transfer',
                'description' => 'Transfer to the official school account and submit your reference.',
                'available' => $bankTransferAvailable,
            ],
            [
                'value' => 'ussd',
                'label' => 'USSD',
                'description' => 'Complete payment using a supported bank USSD channel.',
                'available' => $this->providerFor('ussd') !== null,
            ],
            [
                'value' => 'wallet',
                'label' => 'Wallet',
                'description' => 'Pay using an enabled OPay or PalmPay wallet checkout.',
                'available' => $this->providerFor('wallet') !== null,
            ],
        ]);
    }
}
