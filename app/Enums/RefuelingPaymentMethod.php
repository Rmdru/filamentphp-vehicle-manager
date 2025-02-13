<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RefuelingPaymentMethod: string implements HasLabel, HasIcon
{
    case Cash = 'cash';
    case BankCard = 'bank_card';
    case LoyaltyProgram = 'loyalty_program';
    case FuelCard = 'fuel_card';
    case App = 'app';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::BankCard => __('Bank card'),
            self::LoyaltyProgram => __('Loyalty program'),
            self::FuelCard => __('Fuel card'),
            self::App => __('App'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Cash => 'mdi-hand-coin-outline',
            self::BankCard => 'gmdi-credit-card',
            self::LoyaltyProgram => 'mdi-gift',
            self::FuelCard => 'gmdi-local-gas-station-r',
            self::App => 'mdi-cellphone-wireless',
        };
    }
}
