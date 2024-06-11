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
                ->icon('mdi-hand-coin-outline'),
            Stat::make(__('Average costs per kilometer'), '€ ' . round($this->calculateAverageMonthlyCosts() / $this->calculateAverageMonthlyDistance(), 2))
                ->icon('uni-euro-circle-o'),
            Stat::make(__('Average fuel usage'), $this->calculateAverageFuelUsage() . ' l/100km')
                ->icon('gmdi-local-gas-station-r')
                ->chart(Refueling::limit(10)->orderByDesc('date')->pluck('fuel_consumption')->toArray()),
        ];
    }

    private function calculateAverageMonthlyCosts(): int
    {
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

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

    private function calculateAverageMonthlyDistance(): int
    {
        $vehicleId = $this->filters['vehicle_id'] ?? Vehicle::first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

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

    private function calculateAverageFuelUsage(): float
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
}
