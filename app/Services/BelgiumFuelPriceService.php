<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class BelgiumFuelPriceService
{
    public function crawlBelgiumFuelPrices(): array
    {
        return $this->crawl();
    }

    private function crawl(): array
    {
        $response = Http::timeout(5)
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
            ->retry(3, 100)
            ->get(config('belgium_fuel_prices.crawl_url'));

        if (! $response->ok()) {
            return [];
        }
        
        $crawler = new Crawler($response->body());

        $prices = [];

        $crawler->filter('table.prix-officiel tbody tr')->each(function (Crawler $row) use (&$prices) {
            $fuelType = trim($row->filter('td')->eq(0)->text());
            $priceRaw = trim($row->filter('td.price')->text());

            $price = str_replace(['€', '/l', ' '], '', $priceRaw);
            $price = str_replace(',', '.', $price);

            if (! in_array($fuelType, config('belgium_fuel_prices.fuel_types'))) {
                return;
            }

            $prices[] = [
                'fuel_type' => $fuelType,
                'price' => (float) $price,
            ];
        });

        return $prices;
    }
}