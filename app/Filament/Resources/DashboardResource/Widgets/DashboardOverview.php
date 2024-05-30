<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

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

            $averageMonthlyCost = $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
        } else {
            $averageMonthlyCost = 0;
        }

        return [
            Stat::make(__('Average costs'), '€ ' . $averageMonthlyCost),
        ];
    }
}
