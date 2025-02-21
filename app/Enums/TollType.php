<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TollType: string implements HasLabel, HasIcon
{
    case Location = 'location';
    case Section = 'section';

    public function getLabel(): string
    {
        return match ($this) {
            self::Location => __('Location'),
            self::Section => __('Section'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Location => 'gmdi-location-on-r',
            self::Section => 'gmdi-route-r',
        };
    }
}
