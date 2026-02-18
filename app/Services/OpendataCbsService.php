<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpendataCbsService
{
    public function fetchDutchFuelPrices(): string
    {
        return $this->call('80416ENG/TypedDataSet');
    }

    private function call(string $endpoint, array $params = []): string
    {
        $response = Http::timeout(30)
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
            ->retry(3, 1000)
            ->get(config('opendata_cbs.base_url') . $endpoint, $params);

        if ($response->ok()) {
            return $response->body();
        }

        return '';
    }
}