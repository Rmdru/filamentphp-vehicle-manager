<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Models\FuelDetourAggregate;
use App\Models\FuelPrice;
use App\Models\Vehicle;
use App\Traits\VehicleStats;

class CalculateFuelDetourAggregates {
    use VehicleStats;

    public function handle($data, $next): array
    {
        Vehicle::query()
            ->chunkById(100, function ($vehicles) use ($data) {
                foreach ($vehicles as $vehicle) {
                    if (in_array($vehicle->powertrain, ['electricity', 'hydrogen'])) {
                        continue;
                    }

                    foreach ($data as $fuelPrice) {
                        if ($fuelPrice['country'] === 'netherlands') {
                            continue;
                        }

                        $fuelPriceId = FuelPrice::query()
                            ->where('country', $fuelPrice['country'])
                            ->where('fuel_type', $fuelPrice['fuel_type'])
                            ->whereDate('date', $fuelPrice['date'])
                            ->first('id')
                            ->id;

                        $avgFuelCostsPerKm = $this->calculateAverageFuelCostsPerKilometer($vehicle->id);
                        $avgTotalCostsPerKm = $this->calculateAverageMonthlyCosts(vehicle: $vehicle);

                        $dutchFuelPrice = array_filter($data, function ($item) use ($fuelPrice) {
                            return $item['country'] === 'netherlands' && $item['fuel_type'] === $fuelPrice['fuel_type'];
                        });

                        if (empty($dutchFuelPrice)) {
                            continue;
                        }

                        $priceDiff = ($dutchFuelPrice[array_key_first($dutchFuelPrice)]['price'] - $fuelPrice['price']) * $vehicle->tank_capacity;

                        $maxDetourOnlyFuelCosts = 0;
                        if ($avgFuelCostsPerKm > 0 && $priceDiff > 0) {
                            $maxDetourOnlyFuelCosts = $priceDiff / $avgFuelCostsPerKm;
                        }

                        $maxDetourTotalCosts = 0;
                        if ($avgTotalCostsPerKm > 0 && $priceDiff > 0) {
                            $maxDetourTotalCosts = $priceDiff / $avgTotalCostsPerKm;
                        }

                        FuelDetourAggregate::upsert(
                            [
                                'vehicle_id' => $vehicle->id,
                                'fuel_price_id' => $fuelPriceId,
                                'max_detour_only_fuel_costs' => $maxDetourOnlyFuelCosts,
                                'max_detour_all_costs' => $maxDetourTotalCosts,
                            ],
                            ['fuel_price_id', 'vehicle_id'],
                        );
                    }
                }
            });

        return $next($data);
    }
}