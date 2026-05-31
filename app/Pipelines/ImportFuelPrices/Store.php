<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Models\FuelPrice;
use Illuminate\Support\Facades\Log;

class Store {
    public function handle($data, $next): array
    {
        try {
            FuelPrice::upsert($data, ['date', 'country', 'fuel_type'], ['price']);
            
            Log::channel('fuel-prices')->info('Fuel prices stored', [
                'count' => count($data),
                'records' => json_encode(
                    array_map(fn($d) => "{$d['country']} - {$d['fuel_type']}", $data)
                ),
            ]);
        } catch (\Throwable $e) {
            Log::channel('fuel-prices')->error('Failed to store fuel prices', [
                'message' => $e->getMessage(),
                'count' => count($data),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
        
        return $next($data);
    }
}