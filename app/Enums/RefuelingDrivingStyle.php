<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RefuelingDrivingStyle: string implements HasLabel, HasIcon
{
    case Slow = 'slow';
    case Average = 'average';
    case Fast = 'fast';

    public function getLabel(): string
    {
        return match ($this) {
            self::Slow => __('Slow'),
            self::Average => __('Average'),
            self::Fast => __('Fast'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Slow => 'mdi-speedometer-slow',
            self::Average => 'mdi-speedometer-medium',
            self::Fast => 'mdi-speedometer',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Slow => 'warning',
            self::Average => 'success',
            self::Fast => 'primary',
        };
    }

}
