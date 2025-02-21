<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ParkingType: string implements HasLabel, HasIcon
{
    case Street = 'street';
    case Garage = 'garage';

    public function getLabel(): string
    {
        return match ($this) {
            self::Street => __('Street'),
            self::Garage => __('Parking garage'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Street => 'maki-parking-paid',
            self::Garage => 'maki-parking-garage',
        };
    }
}
