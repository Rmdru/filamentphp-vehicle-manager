<?php

declare(strict_types=1);

namespace App\Support;

class StatusNotification
{
    public static function configuration(): array
    {
        return [
            'insurance_status' => [
                'statusKey' => 'insurance_status',
                'category' => 'insurance',
                'label' => __('Insurance status reminder'),
                'thresholds' => ['critical' => 0, 'warning' => 15, 'info' => 31],
                'thresholdType' => 'time',
                'messages' => [
                    'critical' => __('Vehicle is not insured! Your are currently not allowed to drive with the vehicle!'),
                    'warning' => __('Insurance expires within 2 weeks!'),
                    'info' => __('Insurance expires within 1 month!'),
                ],
                'icon' => 'mdi-shield-car',
            ],
            'tax_reminder' => [
                'statusKey' => 'tax_status',
                'category' => 'tax',
                'label' => __('Road tax period info'),
                'thresholds' => ['info' => 31],
                'thresholdType' => 'time',
                'messages' => [
                    'info' => __('New tax period within 1 month!'),
                ],
                'icon' => 'mdi-highway',
            ],
            'apk' => [
                'statusKey' => 'apk_status',
                'category' => 'maintenance',
                'label' => __('MOT reminder'),
                'thresholds' => ['critical' => 1, 'warning' => 31, 'info' => 62],
                'thresholdType' => 'time',
                'messages' => [
                    'critical' => __('MOT expired! Your are currently not allowed to drive with the vehicle!'),
                    'warning' => __('MOT expires within 1 month!'),
                    'info' => __('MOT expires within 2 months!'),
                ],
                'icon' => 'gmdi-security',
            ],
            'recall' => [
                'statusKey' => 'recall_status',
                'category' => 'maintenance',
                'label' => __('Recall reminder'),
                'thresholds' => ['critical' => 0],
                'thresholdType' => 'recordCount',
                'messages' => [
                    'critical' => __('Open recall! Please contact the dealer or manufactor as soon as possible!'),
                ],
                'icon' => 'mdi-head-sync',
            ],
            'maintenance' => [
                'statusKey' => 'maintenance_status',
                'category' => 'maintenance',
                'label' => __('Maintenance reminder'),
                'thresholds' => ['critical' => 31, 'warning' => 62],
                'thresholdType' => 'time',
                'thresholdCompareKeyTime' => 'minDaysTillDeadline',
                'messages' => [
                    'critical' => __('Maintenance required now'),
                    'warning' => __('Maintenance required soon'),
                ],
                'icon' => 'mdi-car-wrench',
            ],
            'airco_check' => [
                'statusKey' => 'airco_check_status',
                'category' => 'maintenance',
                'label' => __('Airco check reminder'),
                'thresholds' => ['critical' => 31, 'warning' => 62],
                'thresholdType' => 'time',
                'messages' => [
                    'critical' => __('Airco check required!'),
                    'warning' => __('Airco check required soon!'),
                ],
                'icon' => 'mdi-air-conditioner',
            ],
            'old_fuel' => [
                'statusKey' => 'fuel_status',
                'category' => 'refueling',
                'label' => __('Outdated fuel (only E10 fuels)'),
                'thresholds' => ['critical' => 10, 'warning' => 30],
                'thresholdType' => 'time',
                'messages' => [
                    'critical' => __('Fuel is too old!'),
                    'warning' => __('Fuel is getting old!'),
                ],
                'icon' => 'gmdi-local-gas-station-r',
            ],
            'periodic_super_plus' => [
                'statusKey' => 'periodic_super_plus',
                'category' => 'refueling',
                'label' => __('1 in 4 times fill up with Super Plus (E5)'),
                'thresholds' => ['info' => 2],
                'thresholdType' => 'recordCount',
                'messages' => [
                    'info' => __('Next time fill up with Super Plus (E5) fuel!'),
                ],
                'icon' => 'gmdi-local-gas-station-r',
            ],
            'washing_carwash' => [
                'statusKey' => 'carwash_status',
                'category' => 'reconditioning',
                'label' => __('Carwash reminder'),
                'thresholds' => ['warning' => 5, 'info' => 10],
                'thresholdType' => 'time',
                'messages' => [
                    'warning' => __('Carwash required!'),
                    'info' => __('Carwash required soon!'),
                ],
                'icon' => 'mdi-car-wash',
            ],
            'self_washing' => [
                'statusKey' => 'self_washing_status',
                'category' => 'reconditioning',
                'label' => __('Self washing reminder'),
                'thresholds' => ['warning' => 5, 'info' => 10],
                'thresholdType' => 'time',
                'messages' => [
                    'warning' => __('Self washing required!'),
                    'info' => __('Self washing required soon!'),
                ],
                'icon' => 'mdi-car-wash',
            ],
            'self_washing_protection' => [
                'statusKey' => 'self_washing_protection_status',
                'category' => 'reconditioning',
                'label' => __('Self washing with protection reminder'),
                'thresholds' => ['warning' => 5, 'info' => 10],
                'thresholdType' => 'time',
                'messages' => [
                    'warning' => __('Self washing with exterior protection required!'),
                    'info' => __('Self washing with exterior protection required soon!'),
                ],
                'icon' => 'mdi-spray',
            ],
            'tire_pressure_check' => [
                'statusKey' => 'tire_pressure_check_status',
                'category' => 'maintenance',
                'label' => __('Tire pressure check reminder'),
                'thresholds' => ['warning' => 10, 'info' => 20],
                'thresholdType' => 'time',
                'messages' => [
                    'warning' => __('Check tire pressure!'),
                    'info' => __('Check tire pressure soon!'),
                ],
                'icon' => 'mdi-car-tire-alert',
            ],
            'liquids_check' => [
                'statusKey' => 'liquids_check_status',
                'category' => 'maintenance',
                'label' => __('Liquids check reminder'),
                'thresholds' => ['warning' => 5, 'info' => 10],
                'thresholdType' => 'time',
                'messages' => [
                    'warning' => __('Check liquids!'),
                    'info' => __('Check liquids soon!'),
                ],
                'icon' => 'mdi-oil',
            ],
        ];
    }

    public static function types(): array
    {
        return [
            'critical' => [
                'priority' => 0,
                'textColor' => 'text-red-500',
                'borderColor' => 'border-red-500',
                'filamentColor' => 'danger',
                'icon' => 'gmdi-warning-r',
                'badgeText' => __('Attention required'),
            ],
            'warning' => [
                'priority' => 1,
                'textColor' => 'text-orange-400',
                'borderColor' => 'border-orange-500',
                'filamentColor' => 'warning',
                'icon' => 'gmdi-error-r',
                'badgeText' => __('Attention recommended'),
            ],
            'info' => [
                'priority' => 2,
                'textColor' => 'text-blue-400',
                'borderColor' => 'border-blue-400',
                'filamentColor' => 'info',
                'icon' => 'gmdi-info-r',
                'badgeText' => __('Notification'),
            ],
            'success' => [
                'priority' => 3,
                'textColor' => 'text-green-500',
                'borderColor' => 'border-green-500',
                'filamentColor' => 'success',
                'icon' => 'gmdi-check-r',
                'badgeText' => __('OK'),
            ],
        ];
    }
}