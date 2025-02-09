<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VehicleStatus: string implements HasLabel, HasIcon
{
    case Drivable = 'drivable';
    case Suspended = 'suspended';
    case Wok = 'wok';
    case Apk = 'apk';
    case Seized = 'seized';
    case Stolen = 'stolen';
    case Sold = 'sold';
    case NotRollable = 'not_rollable';
    case Destroyed = 'destroyed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Drivable => __('Drivable'),
            self::Suspended => __('Suspended'),
            self::Wok => __('WOK status'),
            self::Apk => __('Invalid MOT'),
            self::Seized => __('Seized'),
            self::Stolen => __('Stolen'),
            self::Sold => __('Sold'),
            self::NotRollable => __('Not rollable'),
            self::Destroyed => __('Destroyed'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Drivable => 'mdi-speedometer',
            self::Suspended => 'mdi-garage',
            self::Wok => 'mdi-shield-off',
            self::Apk => 'mdi-shield-alert',
            self::Seized => 'maki-police',
            self::Stolen => 'mdi-lock-open-alert',
            self::Sold => 'gmdi-local-offer',
            self::NotRollable => 'fas-car-crash',
            self::Destroyed => 'mdi-fire',
        };
    }
}
