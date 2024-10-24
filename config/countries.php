<?php

declare(strict_types=1);

return [
    'netherlands' => [
        'name' => 'Nederland (Netherlands)',
        'iso_code' => 'NL',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-yellow-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-yellow-500',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'warning',
            'backgroundColor' => 'bg-yellow-500',
            'prefix' => 'NL',
            'euBar' => true,
        ],
    ],
    'belgium' => [
        'name' => 'België/Belgique (Belgium)',
        'iso_code' => 'BE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'ring' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-white',
                'prefix' => 'R',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => 'border-red-500',
            'color' => 'text-red-500',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'B',
            'euBar' => true,
        ],
    ],
    'germany' => [
        'name' => 'Deutschland (Germany)',
        'iso_code' => 'DE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-yellow-500',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'D',
            'euBar' => true,
        ],
    ],
    'luxembourg' => [
        'name' => 'Lëtzebuerg (Luxembourg)',
        'iso_code' => 'LU',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'N',
            ],
            'provincial_road' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'warning',
            'backgroundColor' => 'bg-yellow-500',
            'prefix' => 'L',
            'euBar' => true,
        ],
    ],
    'france' => [
        'name' => 'France (France)',
        'iso_code' => 'FR',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-yellow-500',
                'prefix' => 'D',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'F',
            'euBar' => true,
        ],
    ],
    'italy' => [
        'name' => 'Italia (Italy)',
        'iso_code' => 'IT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'SS',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-white',
                'prefix' => 'SP',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'I',
            'euBar' => true,
        ],
    ],
    'spain' => [
        'name' => 'España (Spain)',
        'iso_code' => 'ES',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'AP',
            ],
            'secondary' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-orange-500',
                'prefix' => 'C',
            ],
        ],
        'license_plate' => [
            'border' => 'border-white',
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'E',
            'euBar' => true,
        ],
    ],
    'united_kingdom' => [
        'name' => 'United Kingdom (United Kingdom)',
        'iso_code' => 'GB',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'M',
            ],
            'secondary' => [
                'color' => 'text-yellow-300',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-white',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'warning',
            'backgroundColor' => 'bg-yellow-500',
            'prefix' => null,
            'euBar' => false,
        ],
    ],
    'sweden' => [
        'name' => 'Sverige (Sweden)',
        'iso_code' => 'SE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'S',
            'euBar' => true,
        ],
    ],
    'norway' => [
        'name' => 'Norge (Norway)',
        'iso_code' => 'NO',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'secondary' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
            ],
            'provincial' => [
                'color' => 'text-black',
                'backgroundColor' => 'bg-white',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'N',
            'euBar' => true,
        ],
    ],
    'switzerland' => [
        'name' => 'Schweiz/Suisse/Svizzera (Switzerland)',
        'iso_code' => 'CH',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => null,
            'euBar' => false,
        ],
    ],
    'austria' => [
        'name' => 'Österreich (Austria)',
        'iso_code' => 'AT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-yellow-500',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => 'border-red-500',
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'A',
            'euBar' => true,
        ],
    ],
    'poland' => [
        'name' => 'Polska (Poland)',
        'iso_code' => 'PL',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
                'prefix' => 'S',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-red-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'PL',
            'euBar' => true,
        ],
    ],
    'czech_republic' => [
        'name' => 'Česká republika (Czech Republic)',
        'iso_code' => 'CZ',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'CZ',
            'euBar' => true,
        ],
    ],
    'portugal' => [
        'name' => 'Portugal (Portugal)',
        'iso_code' => 'PT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-white',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'P',
            'euBar' => true,
        ],
    ],
    'greece' => [
        'name' => 'Ελλάδα (Greece)',
        'iso_code' => 'GR',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'provincial_road' => [
                'color' => 'text-white',
                'backgroundColor' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filamentColor' => 'gray',
            'backgroundColor' => 'bg-white',
            'prefix' => 'GR',
            'euBar' => true,
        ],
    ],
];

?>
