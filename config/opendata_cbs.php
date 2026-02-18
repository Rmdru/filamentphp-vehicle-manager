<?php

declare(strict_types=1);

return [
    'base_url' => env('OPENDATA_CBS_BASE_URL', 'https://opendata.cbs.nl/ODataApi/odata/'),
    'fuel_types' => [
        'Unleaded 95 (E10)' => 'Euro95_1',
        'Diesel' => 'Diesel_2',
        'LPG' => 'LPG_3',
    ],
];