<?php

namespace App\Filament\Widgets;

use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class DashboardStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    public function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $vehicle = Filament::getTenant();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

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
                suffix: $powertrain['consumption_unit'],
            ),
            $this->buildStat(
                title: __('Average on-board computer deviation'),
                value: $this->calculateAvgOnboardComputerDeviation(),
                icon: 'mdi-content-save',
                latestValue: $this->calculateAvgOnboardComputerDeviation(true),
                suffix: $powertrain['consumption_unit'],
                operator: '<',
                hide: empty($this->calculateAvgOnboardComputerDeviation())
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
                operator: '>'
            ),
            $this->buildStat(
                title: __('Average speed'),
                value: $this->calculateAvgSpeed(),
                icon: 'mdi-speedometer',
                latestValue: $this->calculateAvgSpeed(true),
                suffix: 'km/h',
                operator: '>',
                hide: empty($this->calculateAvgSpeed())
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
        string $operator = '<',
        bool $hide = false
    ): ?Stat
    {
        if ($hide) {
            return null;
        }

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
        $description = __('Latest:') . ' ' . $prefix . ' ' . $latestValue . ' ' . $suffix;

        return Stat::make($title, $value)
            ->icon($icon)
            ->description($description)
            ->descriptionColor($descriptionColor)
            ->descriptionIcon($descriptionIcon);
    }

    private function calculateAverageMonthlyCosts(bool $thisMonth = false): int
    {
        $vehicle = Filament::getTenant();
        $startDate = $this->filters['startDate'] ?? '';
        $endDate = $this->filters['endDate'] ?? '';

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $costData = $vehicle->calculateMonthlyCosts($startDate, $endDate);

        $totalCosts = 0;
        $uniqueMonths = count($costData['labels']);

        foreach ($costData['monthlyCosts'] as $costs) {
            foreach ($costs as $cost) {
                $totalCosts += $cost;
            }
        }

        return $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
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
        $vehicleId = Filament::getTenant()->id;
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
        $vehicleId = Filament::getTenant()->id;
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
            return round($refuelings->latest()->first()->fuel_consumption, 2);
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

        $refuelings = Refueling::where('vehicle_id', $vehicleId)
            ->whereNotNull('fuel_consumption_onboard_computer');

        if (! $refuelings->count()) {
            return 0.0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        if ($latest) {
            $latestRefueling = $refuelings->latest()->first();

            if ($latestRefueling) {
                $deviation = $latestRefueling->fuel_consumption - $latestRefueling->fuel_consumption_onboard_computer;

                return round($deviation, 3);
            }

            return 0.0;
        }

        $refuelingsData = $refuelings->get();
        if ($refuelingsData->isEmpty()) {
            return 0.0;
        }

        $totalDeviation = 0.0;
        foreach ($refuelingsData as $refueling) {
            $totalDeviation += $refueling->fuel_consumption - $refueling->fuel_consumption_onboard_computer;
        }

        $averageDeviation = $totalDeviation / $refuelingsData->count();

        return round($averageDeviation, 3);
    }
}
