<?php

declare(strict_types=1);

namespace App\Pipelines\ImportFuelPrices;

use App\Services\OpendataCbsService;
use Carbon\Carbon;

class Netherlands {
    public function handle($data, $next): array
    {
        $apiData = json_decode((new OpendataCbsService)->fetchDutchFuelPrices(), true);
        $latest = $apiData['value'][array_key_last($apiData['value'])];
        $config = config('opendata_cbs');

        foreach ($config['fuel_types'] as $key => $column) {
            $data[] = [
                'date' => Carbon::parse($latest['Periods']),
                'country' => 'netherlands',
                'fuel_type' => $key,
                'price' => $latest[$column],
            ];
        }

        return $next($data);
    }
}