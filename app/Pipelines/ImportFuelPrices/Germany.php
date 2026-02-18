<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Services\TankerkoenigService;
use Carbon\Carbon;

class Germany {
    public function handle($data, $next): array
    {
        $apiData = json_decode((new TankerkoenigService)->fetchGermanFuelPrices(), true);
        $config = config('tankerkoenig');

        foreach ($config['fuel_types'] as $key => $column) {
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