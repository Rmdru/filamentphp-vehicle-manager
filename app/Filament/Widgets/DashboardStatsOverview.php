<?php

namespace App\Filament\Widgets;

use App\Models\Insurance;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        return [
            $this->buildStat(
                title: __('Average monthly costs'),
                value: $this->calculateAverageMonthlyCosts(),
                icon: 'mdi-hand-coin-outline',
                latestValue: $this->calculateAverageMonthlyCosts(true),
                prefix: '€',
            ),
            $this->buildStat(
                title: __('Average costs per kilometer'),
                value: $this->calculateCostsPerKilometer(),
                icon: 'uni-euro-circle-o',
                latestValue: $this->calculateCostsPerKilometer(true),
                prefix: '€',
                suffix: '/km',
            ),
            $this->buildStat(
                title: __('Average fuel usage'),
                value: $this->calculateAverageFuelConsumption(),
                icon: 'gmdi-local-gas-station-r',
                latestValue: $this->calculateAverageFuelConsumption(true),
                suffix: 'l/100km',
            ),
            $this->buildStat(
                title: __('Average range'),
                value: $this->calculateAverageRange(),
                icon: 'gmdi-route-r',
                latestValue: $this->calculateAverageRange(true),
                suffix: 'km',
                operator: '>'
            ),
            $this->buildStat(
                title: __('Average monthly distance'),
                value: $this->calculateAverageMonthlyDistance(),
                icon: 'mdi-map-marker-distance',
                latestValue: $this->calculateAverageMonthlyDistance(true),
                suffix: 'km',
            ),
            $this->buildStat(
                title: __('Average speed'),
                value: $this->calculateAvgSpeed(),
                icon: 'mdi-speedometer',
                latestValue: $this->calculateAvgSpeed(true),
                suffix: 'km/h',
                operator: '>'
            ),
        ];
    }

    private function buildStat(
        string $title,
        string $value,
        string $icon,
        string $latestValue,
        string $prefix = '',
        string $suffix = '',
        string $operator = '<'
    ): Stat
    {
        if ($operator === '<') {
            $descriptionColor = ($latestValue < $value) ? 'success' : 'danger';
            $descriptionIcon = ($latestValue < $value) ? 'gmdi-trending-down-r' : 'gmdi-trending-up-r';
        }
        if ($operator === '>') {
            $descriptionColor = ($latestValue > $value) ? 'success' : 'danger';
            $descriptionIcon = ($latestValue > $value) ? 'gmdi-trending-up-r' : 'gmdi-trending-down-r';
        }

        if ($latestValue === $value) {
            $descriptionColor = 'gray';
            $descriptionIcon = 'mdi-approximately-equal';
        }

        $value = $prefix . ' ' . $value . ' ' . $suffix;
        $latestValue = __('Latest:') . ' ' . $prefix . ' ' . $latestValue . ' ' . $suffix;

        return Stat::make($title, $value)
            ->icon($icon)
            ->description($latestValue)
            ->descriptionColor($descriptionColor)
            ->descriptionIcon($descriptionIcon);
    }

    private function calculateAverageMonthlyCosts(bool $thisMonth = false): int
    {
        $vehicleId = Vehicle::selected()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $vehicle = Vehicle::where('id', $vehicleId)
            ->with([
                'maintenances',
                'refuelings',
                'insurances',
                'taxes',
                'parkings',
                'tolls',
                'fines',
            ])
            ->first();

        if ($vehicle) {
            $maintenances = $vehicle->maintenances;
            $refuelings = $vehicle->refuelings;
            $insurances = $vehicle->insurances;
            $taxes = $vehicle->taxes;
            $parkings = $vehicle->parkings;
            $tolls = $vehicle->tolls;
            $fines = $vehicle->fines;

            if ($startDate) {
                $maintenances = $maintenances->where('date', '>=', $startDate);
                $refuelings = $refuelings->where('date', '>=', $startDate);
                $parkings = $parkings->where('end_time', '>=', $startDate);
                $tolls = $tolls->where('date', '>=', $startDate);
                $fines = $fines->where('date', '>=', $startDate);
            }

            if ($startDate && ! $thisMonth) {
                $insurances = $insurances->where('start_date', '>=', $startDate);
                $taxes = $taxes->where('start_date', '>=', $startDate);
            }

            if ($endDate) {
                $maintenances = $maintenances->where('date', '<=', $endDate);
                $refuelings = $refuelings->where('date', '<=', $endDate);
                $parkings = $parkings->where('end_time', '<=', $endDate);
                $tolls = $tolls->where('date', '<=', $endDate);
                $fines = $fines->where('date', '<=', $endDate);
            }

            if ($endDate && ! $thisMonth) {
                $insurances = $insurances->where('end_date', '<=', $endDate);
                $taxes = $insurances->where('end_date', '<=', $endDate);
            }


            $totalInsurancePrice = 0;
            $totalInsuranceMonths = collect();
            $totalTaxPrice = 0;
            $totalTaxMonths = collect();

            foreach ($insurances as $insurance) {
                if (! $insurance) {
                    $insurance = new Insurance();

                    $insurance->months = collect();
                    $insurance->price = 0;
                }

                $totalInsuranceMonths = $totalInsuranceMonths->merge($insurance->months);
                $totalInsurancePrice += $insurance->months->count() * $insurance->price;
            }

            foreach ($taxes as $tax) {
                if (! $tax) {
                    $tax = new Insurance();

                    $tax->months = collect();
                    $tax->price = 0;
                }

                $totalTaxMonths = $totalTaxMonths->merge($tax->months);
                $totalTaxPrice += $tax->months->count() * $tax->price;
            }

            if ($thisMonth) {
                $totalInsuranceMonths = $totalInsuranceMonths->last();
                $totalInsurancePrice = $insurances->first()->price;

                $totalTaxMonths = $totalTaxMonths->last();
                $totalTaxPrice = $taxes->first()->price;
            }

            $totalCosts = $maintenances->sum('total_price') + $refuelings->sum('total_price')
                + $totalInsurancePrice + $totalTaxPrice + $parkings->sum('price') + $tolls->sum('price')
                + $fines->sum('price');

            $maintenanceMonths = $maintenances->pluck('date');
            $refuelingMonths = $refuelings->pluck('date');
            $parkingMonths = $parkings->pluck('end_time');
            $tollMonths = $tolls->pluck('date');
            $fineMonths = $fines->pluck('date');

            $uniqueMonths = $maintenanceMonths->merge($refuelingMonths)
                ->merge($totalTaxMonths)
                ->merge($totalInsuranceMonths)
                ->merge($parkingMonths)
                ->merge($tollMonths)
                ->merge($fineMonths)
                ->groupBy(function ($month) {
                    return Carbon::parse($month)->format('Y-m');
                })->count();

            return $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
        } else {
            return 0;
        }
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

            return round($rawCostsPerKilometerCurrentMonth, 3);
        }

        $rawCostsPerKilometer = 0;

        if ($averageMonthlyDistance > 0) {
            $rawCostsPerKilometer = $averageMonthlyCosts / $averageMonthlyDistance;
        }

        return round($rawCostsPerKilometer, 3);
    }

    private function calculateAverageMonthlyDistance(bool $thisMonth = false): int
    {
        $vehicleId = Vehicle::selected()->first()->id;
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

    private function calculateAverageFuelConsumption(bool $latest = false): float
    {
        $vehicleId = Vehicle::selected()->first()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return 0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        if ($latest) {
            return round($refuelings->latest()->first()->fuel_consumption, 1);
        }

        return round($refuelings->get()->avg('fuel_consumption'), 2);
    }

    private function calculateAvgSpeed(bool $latest = false): int
    {
        $refuelings = $this->getRefuelings();

        if (empty($refuelings)) {
            return 0;
        }

        if ($latest) {
            return round($refuelings->latest()->first()->avg_speed, 1);
        }

        return round($refuelings->get()->avg('avg_speed'), 1);
    }

    private function getRefuelings(): ?Builder
    {
        $vehicleId = Vehicle::selected()->first()->id;
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

        if ($latest) {
            $fuelConsumption = $this->calculateAverageFuelConsumption(true);
        }

        $tankCapacity = 35;
        $avgRange = $tankCapacity / $fuelConsumption * 100;

        return round($avgRange);
    }
}
