<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Cost;
use Carbon\Carbon;

class VehicleCostsService
{
    private array $types = [];

    public function __construct()
    {
        $this->types = Cost::types();
    }

    public function getMonths(string $vehicleId): array
    {
        $dates = collect();

        foreach ($this->types as $config) {
            $model = $config['model'];
            $dateColumn = $config['dateColumn'] ?? 'date';

            $firstRecord = $model::where('vehicle_id', $vehicleId)->orderBy($dateColumn, 'asc')->first();
            $lastRecord = $model::where('vehicle_id', $vehicleId)->orderBy($dateColumn, 'desc')->first();

            if ($firstRecord) {
                $dates->push(Carbon::parse($firstRecord->$dateColumn));
            }

            if ($lastRecord) {
                $dates->push(Carbon::parse($lastRecord->$dateColumn));
            }
        }

        if ($dates->isNotEmpty()) {
            $startDate = $dates->min()->startOfMonth();
            $endDate = $dates->max()->endOfMonth();
        } else {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}