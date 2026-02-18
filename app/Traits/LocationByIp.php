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
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
            ->retry(3, 100)
            ->get(config('ip_api.base_url') . $ipAddress);

        if ($response->ok()) {
            $data = $response->json();

            if ($data['status'] === 'success') {
                return $data;
            }
        }

        return null;
    }
}