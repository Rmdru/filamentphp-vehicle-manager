<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenMeteoService
{
    public function fetchHistoricalMinTempsInDateRange(array $ipLocation, string $startDate, string $endDate): array
    {
        $response = Http::timeout(5)
            ->retry(3, 100)
            ->get(config('open_meteo.base_url'), [
                'latitude' => $ipLocation['lat'],
                'longitude' => $ipLocation['lon'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'timezone' => 'auto',
                'daily' => 'temperature_2m_min',
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}