<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Accident;
use App\Models\EnvironmentalSticker;
use App\Models\Ferry;
use App\Models\Fine;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Parking;
use App\Models\Product;
use App\Models\Reconditioning;
use App\Models\Refueling;
use App\Models\Service;
use App\Models\Tax;
use App\Models\Toll;
use App\Models\Vehicle;
use App\Models\Vignette;
use Filament\Facades\Filament;

class Cost
{
    public static function types(): array
    {
        $vehicle = Filament::getTenant();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        return [
            'Fuel' => [
                'model' => Refueling::class,
                'priceField' => 'total_price',
                'dateColumn' => 'date',
                'itemField' => 'CONCAT(amount, " ' . $powertrain['unit_short'] . '")',
                'link' => 'refuelings',
                'icon' => 'gmdi-local-gas-station-r',
            ],
            'Maintenance' => [
                'model' => Maintenance::class,
                'priceField' => 'total_price',
                'dateColumn' => 'date',
                'itemField' => 'description',
                'link' => 'maintenances',
                'icon' => 'mdi-car-wrench',
            ],
            'Reconditioning' => [
                'model' => Reconditioning::class,
                'priceField' => 'price',
                'dateColumn' => 'date',
                'itemField' => 'REPLACE(type, "_", " ")',
                'link' => 'reconditionings',
                'icon' => 'mdi-car-wash',
            ],
            'Insurance' => [
                'model' => Insurance::class,
                'priceField' => 'price',
                'dateColumn' => 'start_date',
                'itemField' => 'type',
                'link' => 'insurances',
                'monthly' => true,
                'invoiceDates' => 'DATE_FORMAT(CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE()), "-", invoice_day), "%Y-%m-%d")',
                'icon' => 'mdi-shield-car',
            ],
            'Tax' => [
                'model' => Tax::class,
                'priceField' => 'price',
                'dateColumn' => 'start_date',
                'link' => 'taxes',
                'monthly' => true,
                'invoiceDates' => 'DATE_FORMAT(CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE()), "-", invoice_day), "%Y-%m-%d")',
                'icon' => 'mdi-highway',
            ],
            'Parking' => [
                'model' => Parking::class,
                'priceField' => 'price',
                'dateColumn' => 'end_time',
                'itemField' => 'location',
                'link' => 'parking',
                'icon' => 'fas-parking',
            ],
            'Toll' => [
                'model' => Toll::class,
                'priceField' => 'price',
                'dateColumn' => 'date',
                'itemField' => "CASE 
                    WHEN end_location IS NOT NULL AND end_location != '' 
                    THEN CONCAT(start_location, ' - ', end_location) 
                    ELSE start_location 
                END",
                'link' => 'toll',
                'icon' => 'maki-toll',
            ],
            'Fine' => [
                'model' => Fine::class,
                'priceField' => 'price',
                'dateColumn' => 'date',
                'itemField' => 'fact',
                'link' => 'fines',
                'icon' => 'maki-police',
            ],
            'Vignette' => [
                'model' => Vignette::class,
                'priceField' => 'price',
                'dateColumn' => 'start_date',
                'itemField' => 'country',
                'link' => 'vignettes',
                'icon' => 'mdi-sticker-text',
            ],
            'Environmental sticker' => [
                'model' => EnvironmentalSticker::class,
                'priceField' => 'price',
                'dateColumn' => 'start_date',
                'itemField' => 'country',
                'link' => 'enironmental-stickers',
                'icon' => 'fas-leaf',
            ],
            'Ferry' => [
                'model' => Ferry::class,
                'priceField' => 'price',
                'dateColumn' => 'start_date',
                'itemField' => "CONCAT(start_location, ' - ', end_location)",
                'link' => 'ferries',
                'icon' => 'mdi-ferry',
            ],
            'Product' => [
                'model' => Product::class,
                'priceField' => 'price',
                'dateColumn' => 'date',
                'itemField' => 'name',
                'link' => 'products',
                'icon' => 'mdi-oil',
            ],
            'Service' => [
                'model' => Service::class,
                'priceField' => 'price',
                'dateColumn' => 'date',
                'itemField' => 'REPLACE(type, "_", " ")',
                'link' => 'services',
                'icon' => 'mdi-tow-truck',
            ],
            'Accident' => [
                'model' => Accident::class,
                'priceField' => 'total_price',
                'dateColumn' => 'datetime',
                'itemField' => 'REPLACE(type, "_", " ")',
                'link' => 'accidents',
                'icon' => 'fas-car-crash',
            ],
        ];
    }
}