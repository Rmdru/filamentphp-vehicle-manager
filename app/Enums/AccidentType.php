<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AccidentType: string implements HasLabel, HasIcon
{
    case OneSided = 'one_sided';
    case MultiSide = 'multi_sided';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OneSided => __('One-sided'),
            self::MultiSide => __('Multi-sided'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OneSided => 'mdi-car',
            self::MultiSide => 'mdi-car-multiple',
        };
    }
}
