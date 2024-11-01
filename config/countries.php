<?php

declare(strict_types=1);

return [
    'netherlands' => [
        'name' => 'Nederland (Netherlands)',
        'iso_code' => 'NL',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-black',
                'background_color' => 'bg-yellow-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-yellow-500',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'warning',
            'background_color' => 'bg-yellow-500',
            'prefix' => 'NL',
            'eu_bar' => true,
        ],
    ],
    'belgium' => [
        'name' => 'België/Belgique (Belgium)',
        'iso_code' => 'BE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'ring' => [
                'color' => 'text-black',
                'background_color' => 'bg-white',
                'prefix' => 'R',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => 'border-red-500',
            'color' => 'text-red-500',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'B',
            'eu_bar' => true,
        ],
    ],
    'germany' => [
        'name' => 'Deutschland (Germany)',
        'iso_code' => 'DE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-yellow-500',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'D',
            'eu_bar' => true,
        ],
    ],
    'luxembourg' => [
        'name' => 'Lëtzebuerg (Luxembourg)',
        'iso_code' => 'LU',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'N',
            ],
            'provincial_road' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'warning',
            'background_color' => 'bg-yellow-500',
            'prefix' => 'L',
            'eu_bar' => true,
        ],
    ],
    'france' => [
        'name' => 'France (France)',
        'iso_code' => 'FR',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-black',
                'background_color' => 'bg-red-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-yellow-500',
                'prefix' => 'D',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'F',
            'eu_bar' => true,
        ],
    ],
    'italy' => [
        'name' => 'Italia (Italy)',
        'iso_code' => 'IT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'SS',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-white',
                'prefix' => 'SP',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'I',
            'eu_bar' => true,
        ],
    ],
    'spain' => [
        'name' => 'España (Spain)',
        'iso_code' => 'ES',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'AP',
            ],
            'secondary' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'N',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-orange-500',
                'prefix' => 'C',
            ],
        ],
        'license_plate' => [
            'border' => 'border-white',
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'E',
            'eu_bar' => true,
        ],
    ],
    'united_kingdom' => [
        'name' => 'United Kingdom (United Kingdom)',
        'iso_code' => 'GB',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'M',
            ],
            'secondary' => [
                'color' => 'text-yellow-300',
                'background_color' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-white',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'warning',
            'background_color' => 'bg-yellow-500',
            'prefix' => null,
            'eu_bar' => false,
        ],
    ],
    'sweden' => [
        'name' => 'Sverige (Sweden)',
        'iso_code' => 'SE',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'S',
            'eu_bar' => true,
        ],
    ],
    'norway' => [
        'name' => 'Norge (Norway)',
        'iso_code' => 'NO',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'secondary' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
            ],
            'provincial' => [
                'color' => 'text-black',
                'background_color' => 'bg-white',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'N',
            'eu_bar' => true,
        ],
    ],
    'switzerland' => [
        'name' => 'Schweiz/Suisse/Svizzera (Switzerland)',
        'iso_code' => 'CH',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => null,
            'eu_bar' => false,
        ],
    ],
    'austria' => [
        'name' => 'Österreich (Austria)',
        'iso_code' => 'AT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-yellow-500',
                'prefix' => 'B',
            ],
        ],
        'license_plate' => [
            'border' => 'border-red-500',
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'A',
            'eu_bar' => true,
        ],
    ],
    'poland' => [
        'name' => 'Polska (Poland)',
        'iso_code' => 'PL',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'A',
            ],
            'secondary' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
                'prefix' => 'S',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-red-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'PL',
            'eu_bar' => true,
        ],
    ],
    'czech_republic' => [
        'name' => 'Česká republika (Czech Republic)',
        'iso_code' => 'CZ',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'E',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'CZ',
            'eu_bar' => true,
        ],
    ],
    'portugal' => [
        'name' => 'Portugal (Portugal)',
        'iso_code' => 'PT',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
                'prefix' => 'A',
            ],
            'provincial' => [
                'color' => 'text-white',
                'background_color' => 'bg-white',
                'prefix' => 'N',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'P',
            'eu_bar' => true,
        ],
    ],
    'greece' => [
        'name' => 'Ελλάδα (Greece)',
        'iso_code' => 'GR',
        'road_types' => [
            'highway' => [
                'color' => 'text-white',
                'background_color' => 'bg-green-600',
                'prefix' => 'A',
            ],
            'provincial_road' => [
                'color' => 'text-white',
                'background_color' => 'bg-blue-500',
            ],
        ],
        'license_plate' => [
            'border' => null,
            'color' => 'text-black',
            'filament_color' => 'gray',
            'background_color' => 'bg-white',
            'prefix' => 'GR',
            'eu_bar' => true,
        ],
    ],
];

?>
