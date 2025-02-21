<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TollPaymentCircumstances: string implements HasLabel, HasIcon
{
    case TollGate = 'toll_gate';
    case Camera = 'camera';

    public function getLabel(): string
    {
        return match ($this) {
            self::TollGate => __('Toll gate'),
            self::Camera => __('Camera'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TollGate => 'maki-toll',
            self::Camera => 'iconpark-surveillancecamerastwo',
        };
    }
}
