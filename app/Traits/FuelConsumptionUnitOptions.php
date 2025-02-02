<?php

declare(strict_types=1);

namespace App\Traits;

trait FuelConsumptionUnitOptions
{
    public function getFuelConsumptionUnitOptions(): array
    {
        $powertrains = trans('powertrains');
        $fuelConsumptionUnits = [];

        foreach ($powertrains as $key => $value) {
            $fuelConsumptionUnits[$key] = $value['fuel_consumption_unit'] ?? 'l/100km';
        }

        return $fuelConsumptionUnits;
    }
}
