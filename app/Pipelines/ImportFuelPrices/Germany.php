<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Services\TankerkoenigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Germany {
    public function handle($data, $next): array
    {
        $apiData = json_decode((new TankerkoenigService)->fetchGermanFuelPrices(), true);
        
        if ($apiData === null || ! isset($apiData['timestamp'])) {
            Log::channel('fuel-prices')->warning('Failed to fetch German fuel prices: Invalid API response');
            return $next($data);
        }
        
        $config = config('tankerkoenig');

        foreach ($config['fuel_types'] as $key => $column) {
            if (! isset($apiData[$column]['median'])) {
                Log::channel('fuel-prices')->warning("Missing German fuel type data: {$key}");
                continue;
            }
            
            $data[] = [
                'date' => Carbon::parse($apiData['timestamp']),
                'country' => 'germany',
                'fuel_type' => $key,
                'price' => $apiData[$column]['median'],
            ];
        }

        return $next($data);
    }
}