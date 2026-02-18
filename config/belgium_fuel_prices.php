<?php

declare(strict_types=1);

return [
    'crawl_url' => env('BELGIUM_FUEL_PRICES_CRAWL_URL', 'https://carbu.com/belgie/index.php/officieleprijs'),
    'fuel_types' => [
        'Unleaded 95 (E10)' => 'Super 95 (E10)',
        'Super Plus' => 'Super 98 (E5)',
        'Diesel' => 'Diesel (B7)',
        'LPG' => 'LPG',
    ],
];