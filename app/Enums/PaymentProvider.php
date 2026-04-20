<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Manual = 'manual';
    case Paystack = 'paystack';
    case PalmPay = 'palmpay';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual Entry',
            self::Paystack => 'Paystack',
            self::PalmPay => 'PalmPay',
        };
    }
}
