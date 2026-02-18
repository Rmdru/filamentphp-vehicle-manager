<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Models\FuelPrice;

class Store {
    public function handle($data, $next): array
    {
        FuelPrice::upsert($data, ['date', 'country', 'fuel_type'], ['price']);
        
        return $next($data);
    }
}