<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RefuelingRoutes: string implements HasLabel, HasIcon
{
    case Motorway = 'motorway';
    case CountryRoad = 'country_road';
    case City = 'city';
    case Trailer = 'trailer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Motorway => __('Motorway'),
            self::CountryRoad => __('Country Road'),
            self::City => __('City'),
            self::Trailer => __('Trailer'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Motorway => 'mdi-highway',
            self::CountryRoad => 'gmdi-landscape-s',
            self::City => 'gmdi-location-city-r',
            self::Trailer => 'mdi-truck-trailer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Motorway => 'info',
            self::CountryRoad => 'success',
            self::City => 'warning',
            self::Trailer => 'danger',
        };
    }
}
