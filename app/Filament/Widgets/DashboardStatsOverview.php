<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Traits\VehicleStats;

class DashboardStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use VehicleStats;

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
                value: round($this->calculateAverageMonthlyCosts(), 2),
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
        string|float $value,
        string $icon,
        string|float $latestValue,
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
}
