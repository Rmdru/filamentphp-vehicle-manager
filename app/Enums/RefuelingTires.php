<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RefuelingTires: string implements HasLabel, HasIcon
{
    case AllSeason = 'all_season';
    case Summer = 'summer';
    case Winter = 'winter';

    public function getLabel(): string
    {
        return match ($this) {
            self::AllSeason => __('All season tires'),
            self::Summer => __('Summer tires'),
            self::Winter => __('Winter tires'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::AllSeason => 'gmdi-sunny-snowing',
            self::Summer => 'gmdi-wb-sunny-o',
            self::Winter => 'forkawesome-snowflake-o',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AllSeason => 'danger',
            self::Summer => 'warning',
            self::Winter => 'info',
        };
    }
}
