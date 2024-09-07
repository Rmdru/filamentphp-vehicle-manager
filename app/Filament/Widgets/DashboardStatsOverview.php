<?php

namespace App\Filament\Widgets;

use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        return [
            $this->buildStat(
                __('Average monthly costs'),
                '€ ' . $this->calculateAverageMonthlyCosts(),
                'mdi-hand-coin-outline',
                $this->calculateAverageMonthlyCosts(true),
            ),
            $this->buildStat(
                __('Average costs per kilometer'),
                '€ ' . $this->calculateCostsPerKilometer() . '/km',
                'uni-euro-circle-o',
                $this->calculateCostsPerKilometer(true),
            ),
            $this->buildStat(
                __('Average fuel usage'),
                $this->calculateAverageFuelConsumption() . ' l/100km',
                'gmdi-local-gas-station-r',
                $this->getLatestFuelConsumption(),
            ),
            $this->buildStat(
                __('Average monthly distance'),
                $this->calculateAverageMonthlyDistance() . ' km',
                'gmdi-route-r',
                $this->calculateAverageMonthlyDistance(true),
            ),
        ];
    }

    private function buildStat(string $title, string $value, string $icon, $latestValue): Stat
    {
        $descriptionColor = ($latestValue <= $value) ? 'success' : 'danger';
        $descriptionIcon = ($latestValue <= $value) ? 'gmdi-trending-down-r' : 'gmdi-trending-up-r';

        return Stat::make($title, $value)
            ->icon($icon)
            ->description(__('Latest:') . ' ' . $latestValue)
            ->descriptionColor($descriptionColor)
            ->descriptionIcon($descriptionIcon);
    }

    private function calculateAverageMonthlyCosts(bool $thisMonth = false): int
    {
        $vehicleId = Vehicle::selected()->latest()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $vehicle = Vehicle::where('id', $vehicleId)
            ->with(['maintenances', 'refuelings'])
            ->first();

        if ($vehicle) {
            $maintenances = $vehicle->maintenances;
            $refuelings = $vehicle->refuelings;

            if ($startDate) {
                $maintenances = $maintenances->where('date', '>=', $startDate);
                $refuelings = $refuelings->where('date', '>=', $startDate);
            }

            if ($endDate) {
                $maintenances = $maintenances->where('date', '<=', $endDate);
                $refuelings = $refuelings->where('date', '<=', $endDate);
            }

            $totalCosts = $maintenances->sum('total_price') + $refuelings->sum('total_price');

            $uniqueMonths = $maintenances->groupBy(function ($maintenance) {
                    return $maintenance->date->format('Y-m');
                })->count() + $refuelings->groupBy(function ($refueling) {
                    return $refueling->date->format('Y-m');
                })->count();

            return $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
        } else {
            return 0;
        }
    }

    private function calculateAverageMonthlyDistance(bool $thisMonth = false): int
    {
        $vehicleId = Vehicle::selected()->latest()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $query = Refueling::query()
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(mileage_end - mileage_begin) as total_distance')
            ->where('vehicle_id', $vehicleId);

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
    }

    private function calculateCostsPerKilometer(bool $thisMonth = false): float
    {
        $averageMonthlyCosts = $this->calculateAverageMonthlyCosts();
        $currentMonthlyCosts = $this->calculateAverageMonthlyCosts(true);
        $averageMonthlyDistance = $this->calculateAverageMonthlyDistance();
        $currentMonthlyDistance = $this->calculateAverageMonthlyDistance(true);

        if ($thisMonth) {
            $rawCostsPerKilometerCurrentMonth = 0;

            if ($currentMonthlyDistance > 0) {
                $rawCostsPerKilometerCurrentMonth = $currentMonthlyCosts / $currentMonthlyDistance;
            }

            return round($rawCostsPerKilometerCurrentMonth,3);
        }

        $rawCostsPerKilometer = 0;

        if ($averageMonthlyDistance > 0) {
            $rawCostsPerKilometer = $averageMonthlyCosts / $averageMonthlyDistance;
        }

        return round($rawCostsPerKilometer,3);
    }

    private function calculateAverageFuelConsumption(): float
    {
        $vehicleId = Vehicle::selected()->latest()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::query()
            ->where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return 0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        return round($refuelings->get()->avg('fuel_consumption'), 1);
    }

    private function getLatestFuelConsumption(): float
    {
        $vehicleId = Vehicle::selected()->latest()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refueling = Refueling::where('vehicle_id', $vehicleId)
            ->orderByDesc('date');

        if (! $refueling->count()) {
            return 0;
        }

        if ($startDate) {
            $refueling->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refueling->whereDate('date', '<=', $endDate);
        }

        return round($refueling->first()->fuel_consumption, 1);
    }
}
