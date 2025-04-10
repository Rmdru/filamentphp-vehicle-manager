<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AccidentSituation: string implements HasLabel, HasIcon
{
    case Injury = 'injury';
    case Fire = 'fire';
    case Entrapment = 'entrapment';
    case VehicleNotRollable = 'vehicle_not_rollable';
    case VehicleInWater = 'vehicle_in_water';
    case Deceased = 'deceased';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Injury => __('Injury'),
            self::Fire => __('Fire'),
            self::Entrapment => __('Entrapment'),
            self::VehicleNotRollable => __('Vehicle not rollable'),
            self::VehicleInWater => __('Vehicle in water'),
            self::Deceased => __('Deceased'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Injury => 'mdi-ambulance',
            self::Fire => 'fas-fire-extinguisher',
            self::Entrapment => 'gmdi-content-cut-r',
            self::VehicleNotRollable => 'mdi-tow-truck',
            self::VehicleInWater => 'fas-hammer',
            self::Deceased => 'mdi-skull-crossbones',
        };
    }
}
