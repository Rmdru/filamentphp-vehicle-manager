<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ParkingPaymentMethod: string implements HasLabel, HasIcon
{
    case Cash = 'cash';
    case BankCard = 'bank_card';
    case App = 'app';
    case Online = 'online';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::BankCard => __('Bank card'),
            self::App => __('App'),
            self::Online => __('Online'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Cash => 'mdi-hand-coin-outline',
            self::BankCard => 'gmdi-credit-card',
            self::App => 'mdi-cellphone-wireless',
            self::Online => 'gmdi-qr-code',
        };
    }
}

