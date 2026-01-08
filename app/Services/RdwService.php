<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RdwService
{
    public function fetchVehicleDataByLicensePlate(string $licensePlate): string
    {
        return $this->baseCall('m9d7-ebf2.json', ['kenteken' => $licensePlate]);
    }
    public function fetchPowertrainDataByLicensePlate(string $licensePlate): string
    {
        return $this->baseCall('8ys7-d773.json', ['kenteken' => $licensePlate]);
    }

    private function baseCall(string $endpoint, array $params): string
    {
        $response = Http::timeout(10)
            ->retry(3, 100)
            ->get(config('rdw.base_url') . $endpoint, $params);

        if ($response->successful()) {
            return $response->body();
        }

        return '';
    }
}