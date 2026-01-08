<?php

declare(strict_types=1);

namespace App\Traits;

trait Vehicles
{
    public static function normalizeLicensePlate(string $licensePlate): string
    {
        return str_replace([' ', '-', '+'], '', strtoupper($licensePlate));
    }
}
