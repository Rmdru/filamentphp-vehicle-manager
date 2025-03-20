<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusNotificationOk: string implements HasLabel
{
    case Vrooom = 'vrooom';
    case LetsGo = 'lets_go';
    case StepOnGas = 'step_on_gas';

    public function getLabel(): string
    {
        return match ($this) {
            self::Vrooom => __('Everything ok! Vrooooooom'),
            self::LetsGo => __('Everything looks good! Let\'s gooo!'),
            self::StepOnGas => __('No notifications, hop hop, step on the gas!'),
        };
    }
}
