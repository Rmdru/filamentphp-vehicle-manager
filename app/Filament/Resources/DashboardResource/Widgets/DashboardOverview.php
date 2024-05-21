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

        $averageMonthlyCost = Vehicle::where('id', $vehicleId)
            ->with(['maintenances', 'refuelings'])
            ->get()
            ->map(function ($vehicle) {
                $maintenances = $vehicle->maintenances();
                $refuelings = $vehicle->refuelings();

                $totalCosts = $maintenances->get()->sum('total_price') + $refuelings->get()->sum('total_price');

                $months = $maintenances->get()->groupBy(function ($maintenance) {
                        return $maintenance->date->format('Y-m');
                    })->count() + $refuelings->get()->groupBy(function ($refueling) {
                        return $refueling->date->format('Y-m');
                    })->count();

                return $totalCosts / $months;
            })
            ->first();

        return [
            Stat::make(__('Average costs'), '€ ' . $averageMonthlyCost),
        ];
    }
}
