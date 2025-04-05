<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServiceType: string implements HasLabel, HasIcon
{
    case TirePressure = 'tire_pressure';
    case RoadsideAssistance = 'roadside_assistance';
    case Salvage = 'salvage';
    case Coffee = 'coffee';
    case Restaurant = 'restaurant';
    case Shop = 'store';
    case Toilet = 'toilet';
    case Hotel = 'hotel';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::TirePressure => __('Tire pressure'),
            self::RoadsideAssistance => __('Roadside assistance'),
            self::Salvage => __('Salvage'),
            self::Coffee => __('Coffee'),
            self::Restaurant => __('Restaurant'),
            self::Shop => __('Shop'),
            self::Toilet => __('Toilet'),
            self::Hotel => __('Hotel'),
            self::Other => __('Other'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TirePressure => 'gmdi-tire-repair-r',
            self::RoadsideAssistance => 'gmdi-phone',
            self::Salvage => 'mdi-tow-truck',
            self::Coffee => 'mdi-coffee',
            self::Restaurant => 'gmdi-restaurant-menu-r',
            self::Shop => 'gmdi-store',
            self::Toilet => 'fas-restroom',
            self::Hotel => 'gmdi-local-hotel-r',
            self::Other => '',
        };
    }
}