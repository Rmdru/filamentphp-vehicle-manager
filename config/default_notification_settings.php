<?php

declare(strict_types=1);

return [
    'maintenance' => [
        'maintenance' => true,
        'recall' => true,
        'apk' => true,
        'airco_check' => true,
        'liquids_check' => true,
        'tire_pressure_check' => true,
    ],
    'reconditioning' => [
        'washing_carwash' => true,
        'self_washing' => true,
        'self_washing_protection' => true,
    ],
    'refueling' => [
        'old_fuel' => true,
        'periodic_super_plus' => true,
    ],
    'insurance' => [
        'insurance_status' => true,
    ],
    'tax' => [
        'tax_reminder' => true,
    ],
];
