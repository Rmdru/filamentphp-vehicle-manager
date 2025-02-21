<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum FineProvider: string implements HasLabel, HasIcon
{
    case Police = 'police';
    case LocalPolice = 'local_police';
    case RoadOperator = 'road_operator';
    case Rdw = 'rdw';
    case OtherGovernment = 'other_government';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Police => __('Police'),
            self::LocalPolice => __('Local police'),
            self::RoadOperator => __('Road operator'),
            self::Rdw => __('National Road Transport Department'),
            self::OtherGovernment => __('Other government agency'),
            self::Other => __('Other'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Police => 'maki-police',
            self::LocalPolice => 'gmdi-local-police',
            self::RoadOperator => 'mdi-highway',
            self::Rdw => 'gmdi-emoji-transportation-r',
            self::OtherGovernment => 'gmdi-account-balance-r',
            default => '',
        };
    }
}