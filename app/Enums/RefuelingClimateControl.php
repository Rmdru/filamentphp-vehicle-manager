<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RefuelingClimateControl: string implements HasLabel, HasIcon
{
    case Automatically = 'automatically';
    case Airco = 'airco';
    case Heater = 'heater';
    case Demisting = 'demisting';
    case Defrost = 'defrost';

    public function getLabel(): string
    {
        return match ($this) {
            self::Automatically => __('Automatically'),
            self::Airco => __('Airco'),
            self::Heater => __('Heater'),
            self::Demisting => __('Demisting'),
            self::Defrost => __('Defrost'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Automatically => 'fas-temperature-high',
            self::Airco => 'mdi-air-conditioner',
            self::Heater => 'mdi-heat-wave',
            self::Demisting => 'mdi-wiper',
            self::Defrost => 'forkawesome-snowflake-o',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Automatically => 'warning',
            self::Airco => 'info',
            self::Heater => 'danger',
            self::Demisting => 'success',
            self::Defrost => 'info',
        };
    }
}
