<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AccidentAttributeType: string implements HasLabel, HasIcon
{
    case Person = 'person';
    case Animal = 'animal';
    case Object = 'object';
    case Building = 'building';
    case Pedestrian = 'pedestrian';
    case Bicycle = 'bicycle';
    case Moped = 'moped';
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Trailer = 'trailer';
    case Truck = 'truck';
    case Tractor = 'tractor';
    case EmergencyVehicle = 'emergency_vehicle';
    case Bus = 'bus';
    case Tram = 'tram';
    case Subway = 'subway';
    case Train = 'train';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Person => __('Person'),
            self::Animal => __('Animal'),
            self::Object => __('Object'),
            self::Building => __('Building'),
            self::Pedestrian => __('Pedestrian'),
            self::Bicycle => __('Bicycle'),
            self::Moped => __('Moped'),
            self::Motorcycle => __('Motorcycle'),
            self::Car => __('Car'),
            self::Trailer => __('Trailer'),
            self::Truck => __('Truck'),
            self::Tractor => __('Tractor'),
            self::EmergencyVehicle => __('Emergency vehicle'),
            self::Bus => __('Bus'),
            self::Tram => __('Tram'),
            self::Subway => __('Subway'),
            self::Train => __('Train'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Person => 'gmdi-person',
            self::Animal => 'tabler-deer',
            self::Object => 'fas-tree',
            self::Building => 'mdi-office-building',
            self::Pedestrian => 'gmdi-directions-walk-r',
            self::Bicycle => 'mdi-bicycle',
            self::Moped => 'gmdi-moped',
            self::Motorcycle => 'mdi-motorbike',
            self::Car => 'mdi-car',
            self::Trailer => 'mdi-truck-trailer',
            self::Truck => 'mdi-truck',
            self::Tractor => 'mdi-tractor',
            self::EmergencyVehicle => 'mdi-car-emergency',
            self::Bus => 'mdi-bus',
            self::Tram => 'gmdi-tram',
            self::Subway => 'mdi-subway',
            self::Train => 'gmdi-train',
        };
    }
}
