<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RdwService
{
    public function fetchVehicleDataByLicensePlate(string $licensePlate): string
    {
        $response = Http::timeout(10)
            ->retry(3, 100)
            ->get(config('rdw.base_url') . 'm9d7-ebf2.json', [
                'kenteken' => $licensePlate,
            ]);

        if ($response->successful()) {
            return $response->body();
        }

        return '';
    }
}