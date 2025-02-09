<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Support\Colors\Color;
use Spatie\Color\Rgb;

trait GenerateRandomHexColor
{
    public function generateRandomHexColor(): string
    {
        $colors = Color::all();
        $randomColor = $colors[array_rand($colors)];
        return Rgb::fromString('rgb('.$randomColor[array_rand($randomColor)].')')->toHex()->__toString();
    }
}
