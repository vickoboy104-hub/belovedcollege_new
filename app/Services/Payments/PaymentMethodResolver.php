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
            // PalmPay remains a settings/template surface until an official integration is approved.
            'wallet' => [PaymentProvider::OPay],
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
                'description' => 'Transfer from your bank to the official school account.',
                'available' => $bankTransferAvailable,
            ],
            [
                'value' => 'ussd',
                'label' => 'USSD',
                'description' => 'Pay without internet using a supported bank USSD channel.',
                'available' => $this->providerFor('ussd') !== null,
            ],
            [
                'value' => 'wallet',
                'label' => 'Wallet',
                'description' => 'Pay using an enabled OPay wallet checkout.',
                'available' => $this->providerFor('wallet') !== null,
            ],
        ]);
    }
}
