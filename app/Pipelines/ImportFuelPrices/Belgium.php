<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Services\BelgiumFuelPriceService;
use Illuminate\Support\Facades\Log;

class Belgium {
    public function handle($data, $next): array
    {
        $crawledData = (new BelgiumFuelPriceService)->crawlBelgiumFuelPrices();
        
        if (empty($crawledData)) {
            Log::channel('fuel-prices')->warning('Failed to fetch Belgian fuel prices: No data returned');
            return $next($data);
        }
        
        $config = config('belgium_fuel_prices');

        foreach ($crawledData as $fuelType) {
            $fuelTypeKey = array_search($fuelType['fuel_type'], $config['fuel_types']);
            
            if ($fuelTypeKey === false) {
                Log::channel('fuel-prices')->warning("Unknown Belgian fuel type: {$fuelType['fuel_type']}");
                continue;
            }
            
            $data[] = [
                'date' => now(),
                'country' => 'belgium',
                'fuel_type' => $fuelTypeKey,
                'price' => $fuelType['price'],
            ];
        }

        return $next($data);
    }
}