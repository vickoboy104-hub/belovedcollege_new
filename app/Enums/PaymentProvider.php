<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Manual = 'manual';
    case Paystack = 'paystack';
    case PalmPay = 'palmpay';
    case Flutterwave = 'flutterwave';
    case Monnify = 'monnify';
    case OPay = 'opay';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual / Bank Transfer',
            self::Paystack => 'Paystack',
            self::PalmPay => 'PalmPay',
            self::Flutterwave => 'Flutterwave',
            self::Monnify => 'Monnify',
            self::OPay => 'OPay',
        };
    }

    public function isOnline(): bool
    {
        return $this !== self::Manual;
    }
}
