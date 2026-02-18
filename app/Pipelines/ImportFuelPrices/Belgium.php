<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Services\BelgiumFuelPriceService;

class Belgium {
    public function handle($data, $next): array
    {
        $crawledData = (new BelgiumFuelPriceService)->crawlBelgiumFuelPrices();
        $config = config('belgium_fuel_prices');

        foreach ($crawledData as $fuelType) {
            $data[] = [
                'date' => now(),
                'country' => 'belgium',
                'fuel_type' => array_search($fuelType['fuel_type'], $config['fuel_types']),
                'price' => $fuelType['price'],
            ];
        }

        return $next($data);
    }
}