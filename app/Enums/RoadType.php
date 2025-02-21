<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum RoadType: string implements HasColor, HasIcon
{
    case Highway = 'highway';
    case Secondary = 'secondary';
    case Ring = 'ring';
    case Provincial = 'provincial';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Highway => __('Highway'),
            self::Secondary => __('Secondary'),
            self::Ring => __('Ring'),
            self::Provincial => __('Provincial'),
            self::Other => __('Other'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Highway => 'mdi-highway',
            self::Secondary => 'mdi-tunnel',
            self::Ring => 'mdi-reload',
            self::Provincial => 'fas-road',
            default => null,
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Highway => 'danger',
            self::Secondary => 'info',
            self::Ring => 'success',
            self::Provincial => 'warning',
            default => null,
        };
    }
}
