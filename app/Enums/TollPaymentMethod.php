<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum TollPaymentMethod: string implements HasLabel, HasIcon
{
    case Cash = 'cash';
    case BankCard = 'bank_card';
    case Online = 'online';
    case TollBadge = 'toll_badge';
    case App = 'app';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::BankCard => __('Bank card'),
            self::Online => __('Online'),
            self::TollBadge => __('Toll badge'),
            self::App => __('App'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'mdi-hand-coin-outline',
            self::BankCard => 'gmdi-credit-card',
            self::Online => 'gmdi-qr-code',
            self::TollBadge => 'mdi-car-connected',
            self::App => 'mdi-cellphone-wireless',
        };
    }
}
