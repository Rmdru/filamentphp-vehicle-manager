<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReconditioningType: string implements HasLabel
{
    case Carwash = 'carwash';
    case ExteriorCleaning = 'exterior_cleaning';
    case InteriorCleaning = 'interior_cleaning';
    case EngineBayCleaning = 'engine_bay_cleaning';
    case DamageRepair = 'damage_repair';

    public function getLabel(): string
    {
        return match ($this) {
            self::Carwash => __('Carwash'),
            self::ExteriorCleaning => __('Exterior cleaning'),
            self::InteriorCleaning => __('Interior cleaning'),
            self::EngineBayCleaning => __('Engine bay cleaning'),
            self::DamageRepair => __('Damage repair'),
        };
    }
}

