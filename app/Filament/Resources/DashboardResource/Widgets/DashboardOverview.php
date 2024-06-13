<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class DashboardOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        return [
            Stat::make(__('Average monthly costs'), '€ ' . $this->calculateAverageMonthlyCosts())
                ->icon('mdi-hand-coin-outline')
                ->description('Laatste: ' . $this->calculateAverageMonthlyCosts(true))
                ->descriptionColor((min($this->calculateAverageMonthlyCosts(), $this->calculateAverageMonthlyCosts(true)) == $this->calculateAverageMonthlyCosts(true)) ? 'success' : 'danger')
                ->descriptionIcon((min($this->calculateAverageMonthlyCosts(), $this->calculateAverageMonthlyCosts(true)) == $this->calculateAverageMonthlyCosts(true)) ? 'gmdi-trending-down-r' : 'gmdi-trending-up-r'),
            Stat::make(__('Average costs per kilometer'), '€ ' . $this->calculateCostsPerKilometer())
                ->icon('uni-euro-circle-o')
                ->description('Laatste: ' . $this->calculateCostsPerKilometer(true))
                ->descriptionColor((min($this->calculateCostsPerKilometer(), $this->calculateCostsPerKilometer(true)) == $this->calculateCostsPerKilometer(true)) ? 'success' : 'danger')
                ->descriptionIcon((min($this->calculateCostsPerKilometer(), $this->calculateCostsPerKilometer(true)) == $this->calculateCostsPerKilometer(true)) ? 'gmdi-trending-down-r' : 'gmdi-trending-up-r'),
            Stat::make(__('Average fuel usage'), $this->calculateAverageFuelConsumption() . ' l/100km')
                ->icon('gmdi-local-gas-station-r')
                ->description('Laatste: ' . $this->getLatestFuelConsumption() . ' l/100km')
                ->descriptionColor((min($this->calculateAverageFuelConsumption(), $this->getLatestFuelConsumption()) == $this->getLatestFuelConsumption()) ? 'success' : 'danger')
                ->descriptionIcon((min($this->calculateAverageFuelConsumption(), $this->getLatestFuelConsumption()) == $this->getLatestFuelConsumption()) ? 'gmdi-trending-down-r' : 'gmdi-trending-up-r'),
        ];
    }

    private function calculateAverageMonthlyCosts($thisMonth = false): int
    {
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
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

    private function calculateAverageMonthlyDistance($thisMonth = false): int
    {
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
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

    private function calculateCostsPerKilometer($thisMonth = false): float
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
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Refueling::query()
            ->where('vehicle_id', $vehicleId);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return round($query->get()->avg('fuel_consumption'), 1);
    }

    private function getLatestFuelConsumption(): float
    {
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Refueling::query()
            ->where('vehicle_id', $vehicleId)
            ->orderByDesc('date');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return round($query->first()->fuel_consumption, 1);
    }
}
