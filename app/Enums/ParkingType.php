<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ParkingType: string implements HasLabel, HasIcon
{
    case Street = 'street';
    case Garage = 'garage';
    case ParkAndRide = 'park_and_ride';
    case Company = 'company';

    public function getLabel(): string
    {
        return match ($this) {
            self::Street => __('Street'),
            self::Garage => __('Parking garage'),
            self::ParkAndRide => __('P+R'),
            self::Company => __('Company'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Street => 'maki-parking-paid',
            self::Garage => 'maki-parking-garage',
            self::ParkAndRide => 'gmdi-train',
            self::Company => 'mdi-office-building',
        };
    }
}
