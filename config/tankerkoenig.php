<?php

declare(strict_types=1);

return [
    'base_url' => env('TANKERKOENIG_API_BASE_URL', 'https://creativecommons.tankerkoenig.de/api/v4/'),
    'fuel_types' => [
        'Unleaded 95 (E10)' => 'E10',
        'Unleaded 95 (E5)' => 'E5',
        'Diesel' => 'Diesel',
    ],
];