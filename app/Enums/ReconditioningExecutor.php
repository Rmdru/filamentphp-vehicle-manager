<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReconditioningExecutor: string implements HasLabel
{
    case Myself = 'myself';
    case Someone = 'someone';
    case Company = 'company';

    public function getLabel(): string
    {
        return match ($this) {
            self::Myself => __('Myself'),
            self::Someone => __('Someone else'),
            self::Company => __('Company'),
        };
    }
}

