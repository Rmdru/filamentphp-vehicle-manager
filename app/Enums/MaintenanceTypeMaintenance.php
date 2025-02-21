<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MaintenanceTypeMaintenance: string implements HasLabel
{
    case TirePressureChecked = 'tire_pressure_checked';
    case LiquidsChecked = 'liquids_checked';
    case Maintenance = 'maintenance';
    case SmallMaintenance = 'small_maintenance';
    case BigMaintenance = 'big_maintenance';

    public function getLabel(): string
    {
        return match ($this) {
            self::TirePressureChecked => __('Tire pressure checked'),
            self::LiquidsChecked => __('Liquids checked'),
            self::Maintenance => __('Maintenance'),
            self::SmallMaintenance => __('Small maintenance'),
            self::BigMaintenance => __('Big maintenance'),
        };
    }
}
