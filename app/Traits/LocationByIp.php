<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait LocationByIp
{
    public function getLocationDataByIp(string $ipAddress): ?array
    {
        if (! app()->isProduction()) {
            $ipAddress = '';
        }

        $response = Http::timeout(5)
            ->retry(3, 100)
            ->get(config('ip_api.base_url') . $ipAddress);

        if ($response->successful()) {
            $data = $response->json();

            if ($data['status'] === 'success') {
                return $data;
            }
        }

        return null;
    }
}