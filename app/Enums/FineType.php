<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum FineType: string implements HasLabel, HasIcon
{
    case Camera = 'camera';
    case Officer = 'officer';
    case TrafficStop = 'traffic_stop';
    case Automated = 'automated';
    case TrafficControl = 'traffic_control';
    case BorderControl = 'border_control';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Camera => __('Camera'),
            self::Officer => __('Officer'),
            self::TrafficStop => __('Traffic stop'),
            self::Automated => __('Automated'),
            self::TrafficControl => __('Traffic control'),
            self::BorderControl => __('Border control'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Camera => 'iconpark-surveillancecamerastwo',
            self::Officer => 'maki-police',
            self::TrafficStop => 'mdi-car-emergency',
            self::Automated => 'gmdi-timer-s',
            self::TrafficControl => 'mdi-map-marker-radius',
            self::BorderControl => 'fas-road-barrier',
        };
    }
}
