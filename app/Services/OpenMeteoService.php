<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoService
{
    public function fetchHistoricalMinTempsInDateRange(array $ipLocation, string $startDate, string $endDate): array
    {
        try {
            $response = Http::timeout(5)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
                ->retry(3, 100)
                ->get(config('open_meteo.base_url'), [
                    'latitude' => $ipLocation['lat'],
                    'longitude' => $ipLocation['lon'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'timezone' => 'auto',
                    'daily' => 'temperature_2m_min',
                ]);

            if ($response->ok()) {
                return $response->json();
            }

            Log::channel('openmeteo-api')->warning('OpenMeteo API returned a non-success response.', [
                'status' => $response->status(),
                'url' => $response->effectiveUri(),
                'latitude' => $ipLocation['lat'] ?? null,
                'longitude' => $ipLocation['lon'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        } catch (\Throwable $exception) {
            Log::channel('openmeteo-api')->warning('OpenMeteo API call failed.', [
                'message' => $exception->getMessage(),
                'url' => config('open_meteo.base_url'),
                'latitude' => $ipLocation['lat'] ?? null,
                'longitude' => $ipLocation['lon'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        return [];
    }
}