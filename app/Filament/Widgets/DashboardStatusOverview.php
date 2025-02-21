<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatusOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            $this->buildStat(
                __('Maintenance'),
                $this->getMaintenanceStatus(),
                'mdi-car-wrench',
            ),
            $this->buildStat(
                __('MOT'),
                $this->getApkStatus(),
                'gmdi-security',
            ),
        ];
    }

    private function buildStat(string $title, array $value, string $icon): Stat
    {
        return Stat::make($title, $value['primary'] ?? '')
            ->icon($icon)
            ->description($value['secondary'] ?? '');
    }

    private function getMaintenanceStatus(): array
    {
        $maintenanceStatus = Vehicle::selected()->first()->maintenance_status;

        if (! $maintenanceStatus) {
            return [];
        }

        return [
            'primary' => str($maintenanceStatus['timeDiffHumans'])->ucfirst(),
            'secondary' => __('About :distance km', ['distance' => $maintenanceStatus['distance']]),
        ];
    }

    private function getApkStatus(): array
    {
        $timeTillApk = Vehicle::selected()->first()->apk_status;

        if (! $timeTillApk) {
            return [];
        }

        return [
            'primary' => str($timeTillApk['timeDiffHumans'])->ucfirst(),
        ];
    }
}
