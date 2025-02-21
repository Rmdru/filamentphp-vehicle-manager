<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum FineSanction: string implements HasLabel, HasIcon
{
    case WokStatus = 'wok_status';
    case VehicleSeized = 'vehicle_seized';
    case DrivingBan = 'driving_ban';
    case EmgCourse = 'emg_course';
    case DriversLicenseConfiscated = 'drivers_license_confiscated';
    case Arrested = 'arrested';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WokStatus => __('WOK status'),
            self::VehicleSeized => __('Vehicle seized'),
            self::DrivingBan => __('Driving ban'),
            self::EmgCourse => __('EMG course'),
            self::DriversLicenseConfiscated => __('Drivers license confiscated'),
            self::Arrested => __('Arrested'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::WokStatus => 'mdi-shield-alert',
            self::VehicleSeized =>'mdi-tow-truck',
            self::DrivingBan => 'mdi-car-clock',
            self::EmgCourse => 'gmdi-school-r',
            self::DriversLicenseConfiscated => 'mdi-credit-card-lock',
            self::Arrested => 'mdi-handcuffs',
        };
    }
}