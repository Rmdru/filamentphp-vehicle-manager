<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait VehicleStats
{
    private static array $metricCache = [];

    private function rememberVehicleMetric(string $suffix, \Closure $callback, string $vehicleId): mixed
    {
        $cacheKey = "vehicle:{$vehicleId}:{$suffix}";

        if (array_key_exists($cacheKey, self::$metricCache)) {
            return self::$metricCache[$cacheKey];
        }

        self::$metricCache[$cacheKey] = $callback();

        return self::$metricCache[$cacheKey];
    }

    private function calculateAverageMonthlyCosts(bool $thisMonth = false, ?Vehicle $vehicle = null): float
    {
        if (empty($vehicle)) {
            $vehicle = Filament::getTenant();
        }

        $startDate = $this->filters['startDate'] ?? '';
        $endDate = $this->filters['endDate'] ?? '';

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        return $this->rememberVehicleMetric(
            "average-monthly-costs:{$startDate}:{$endDate}:" . $vehicle->id,
            function () use ($vehicle, $startDate, $endDate) {
                $costData = $vehicle->calculateMonthlyCosts($startDate, $endDate);

                $totalCosts = 0;
                $uniqueMonths = count($costData['labels']);

                foreach ($costData['monthlyCosts'] as $costs) {
                    foreach ($costs as $cost) {
                        $totalCosts += $cost;
                    }
                }

                return $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
            },
            $vehicle->id
        );
    }

    private function calculateCostsPerKilometer(bool $thisMonth = false, ?Vehicle $vehicle = null): float
    {
        $averageMonthlyCosts = $this->calculateAverageMonthlyCosts(vehicle: $vehicle);
        $currentMonthlyCosts = $this->calculateAverageMonthlyCosts(true, vehicle: $vehicle);
        $averageMonthlyDistance = $this->calculateAverageMonthlyDistance(vehicle: $vehicle);
        $currentMonthlyDistance = $this->calculateAverageMonthlyDistance(true, vehicle: $vehicle);

        if ($thisMonth) {
            $rawCostsPerKilometerCurrentMonth = 0;

            if ($currentMonthlyDistance > 0) {
                $rawCostsPerKilometerCurrentMonth = $currentMonthlyCosts / $currentMonthlyDistance;
            }

            return round($rawCostsPerKilometerCurrentMonth, 3);
        }

        $rawCostsPerKilometer = 0;

        if ($averageMonthlyDistance > 0) {
            $rawCostsPerKilometer = $averageMonthlyCosts / $averageMonthlyDistance;
        }

        return round($rawCostsPerKilometer, 3);
    }

    private function calculateAverageMonthlyDistance(bool $thisMonth = false, ?Vehicle $vehicle = null): float
    {
        if (empty($vehicle)) {
            $vehicle = Filament::getTenant();
        }

        $vehicleId = $vehicle->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $filterKey = json_encode([$startDate, $endDate, $thisMonth]);

        return $this->rememberVehicleMetric(
            "average-monthly-distance:{$vehicleId}:{$filterKey}",
            function () use ($vehicleId, $startDate, $endDate) {
                $query = Refueling::query()->where('vehicle_id', $vehicleId);

                if (DB::getDriverName() === 'sqlite') {
                    $query->selectRaw("strftime('%Y', date) as year, strftime('%m', date) as month, SUM(mileage_end - mileage_begin) as total_distance");
                } else {
                    $query->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(mileage_end - mileage_begin) as total_distance');
                }

                if ($startDate) {
                    $query->whereDate('date', '>=', $startDate);
                }

                if ($endDate) {
                    $query->whereDate('date', '<=', $endDate);
                }

                $query->groupBy('year', 'month');

                $results = $query->get();

                $totalDistance = 0;
                $monthsCount = $results->count();

                foreach ($results as $result) {
                    $totalDistance += $result->total_distance;
                }

                if ($monthsCount === 0) {
                    return 0;
                }

                $averageMonthlyDistance = $totalDistance / $monthsCount;

                return round($averageMonthlyDistance);
            },
            $vehicle->id
        );
    }

    private function calculateAverageFuelConsumption(bool $latest = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $filterKey = json_encode([$vehicleId, $startDate, $endDate, $latest]);

        return $this->rememberVehicleMetric(
            "average-fuel-consumption:{$filterKey}",
            function () use ($vehicleId, $startDate, $endDate, $latest) {
                $refuelings = Refueling::query()->where('vehicle_id', $vehicleId);

                if ($startDate) {
                    $refuelings->whereDate('date', '>=', $startDate);
                }

                if ($endDate) {
                    $refuelings->whereDate('date', '<=', $endDate);
                }

                if ($latest) {
                    return round((float) ($refuelings->latest()->value('fuel_consumption') ?? 0), 2);
                }

                return round((float) ($refuelings->avg('fuel_consumption') ?? 0), 2);
            },
            $vehicleId
        );
    }

    private function calculateAvgSpeed(bool $latest = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $filterKey = json_encode([$vehicleId, $startDate, $endDate, $latest]);

        return $this->rememberVehicleMetric(
            "average-speed:{$filterKey}",
            function () use ($vehicleId, $startDate, $endDate, $latest) {
                $refuelings = Refueling::query()->where('vehicle_id', $vehicleId);

                if ($startDate) {
                    $refuelings->whereDate('date', '>=', $startDate);
                }

                if ($endDate) {
                    $refuelings->whereDate('date', '<=', $endDate);
                }

                if ($latest) {
                    return round((float) ($refuelings->latest()->value('avg_speed') ?? 0), 1);
                }

                return round((float) ($refuelings->avg('avg_speed') ?? 0), 1);
            },
            $vehicleId
        );
    }

    private function getRefuelings(): ?Builder
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::query()
            ->where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return null;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        return $refuelings;
    }

    private function calculateAverageRange(bool $latest = false): float
    {
        $fuelConsumption = $this->calculateAverageFuelConsumption();

        if (! $fuelConsumption) {
            return 0.0;
        }

        if ($latest) {
            $fuelConsumption = $this->calculateAverageFuelConsumption(true);
        }

        $tankCapacity = Filament::getTenant()->tank_capacity;
        $avgRange = $tankCapacity / $fuelConsumption * 100;

        return round($avgRange);
    }

    private function calculateAvgOnboardComputerDeviation(bool $latest = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $filterKey = json_encode([$vehicleId, $startDate, $endDate, $latest]);

        return $this->rememberVehicleMetric(
            "average-onboard-computer-deviation:{$filterKey}",
            function () use ($vehicleId, $startDate, $endDate, $latest) {
                $refuelings = Refueling::query()
                    ->where('vehicle_id', $vehicleId)
                    ->whereNotNull('fuel_consumption_onboard_computer');

                if ($startDate) {
                    $refuelings->whereDate('date', '>=', $startDate);
                }

                if ($endDate) {
                    $refuelings->whereDate('date', '<=', $endDate);
                }

                if ($latest) {
                    $latestRefueling = $refuelings->latest()->first();

                    if (! $latestRefueling) {
                        return 0.0;
                    }

                    $deviation = $latestRefueling->fuel_consumption - $latestRefueling->fuel_consumption_onboard_computer;

                    return round((float) $deviation, 3);
                }

                $averageDeviation = $refuelings
                    ->selectRaw('AVG(fuel_consumption - fuel_consumption_onboard_computer) as average_deviation')
                    ->value('average_deviation');

                return round((float) ($averageDeviation ?? 0), 3);
            },
            $vehicleId
        );
    }

    public function calculateRatioPremiumFuel(bool $thisMonth = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $filterKey = json_encode([$vehicleId, $startDate, $endDate, $thisMonth]);

        return $this->rememberVehicleMetric(
            "premium-fuel-ratio:{$filterKey}",
            function () use ($vehicleId, $startDate, $endDate) {
                $refuelings = Refueling::query()->where('vehicle_id', $vehicleId);

                if ($startDate) {
                    $refuelings->whereDate('date', '>=', $startDate);
                }

                if ($endDate) {
                    $refuelings->whereDate('date', '<=', $endDate);
                }

                $stats = $refuelings
                    ->selectRaw('COALESCE(SUM(amount), 0) as total_amount, COALESCE(SUM(CASE WHEN fuel_type IN (?, ?, ?, ?, ?) THEN amount ELSE 0 END), 0) as premium_amount', [
                        'Super Plus (E5)',
                        'V-Power 100',
                        'Ultimate 102',
                        'Premium diesel',
                        'Electricity AC',
                    ])
                    ->first();

                $totalAmount = (float) ($stats->total_amount ?? 0);

                if ($totalAmount <= 0) {
                    return 0.0;
                }

                $ratio = ((float) ($stats->premium_amount ?? 0) / $totalAmount) * 100;

                return round($ratio, 1);
            },
            $vehicleId
        );
    }

    public function calculateAverageFuelCostsPerKilometer($vehicleId = null): float
    {
        if (empty($vehicleId)) {
            $vehicleId = Filament::getTenant()->id;
        }

        $filterKey = json_encode([$vehicleId]);

        return $this->rememberVehicleMetric(
            "average-fuel-costs-per-kilometer:{$filterKey}",
            function () use ($vehicleId) {
                $stats = Refueling::query()
                    ->where('vehicle_id', $vehicleId)
                    ->selectRaw('COALESCE(SUM(total_price), 0) as total_costs, COALESCE(SUM(mileage_end - mileage_begin), 0) as total_distance')
                    ->first();

                $totalDistance = (float) ($stats->total_distance ?? 0);

                if ($totalDistance <= 0.0) {
                    return 0.0;
                }

                $totalCosts = (float) ($stats->total_costs ?? 0);
                $costsPerKilometer = $totalCosts / $totalDistance;

                return round($costsPerKilometer, 3);
            },
            $vehicleId
        );
    }
}