<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Vehicle;

trait FuelTypeOptions
{
    public function getFuelTypeOptions(): array
    {
        $vehicle = Vehicle::selected()->onlyDrivable()->first();
        $fuelTypes = trans('fuel_types');
        $fuelTypeOptions = [];

        foreach ($vehicle->fuel_types as $value) {
            $fuelTypeOptions[$value] = $fuelTypes[$value];
        }

        return $fuelTypeOptions;
    }
}
