<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatusOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    private array $statuses = [];

    public function mount(): void
    {
        $this->fillStatuses();
    }

    protected function getStats(): array
    {
        if (empty($this->statuses)) {
            return [
                __('No statuses available to show.'),
            ];
        }

        return collect($this->statuses)
            ->map(fn ($status) => $this->buildStat($status['title'], $status['value'], $status['icon']))
            ->filter()
            ->toArray();
    }

    private function fillStatuses(): void
    {
        $vehicle = Filament::getTenant();

        $this->getMaintenanceStatus($vehicle);
        $this->getApkStatus($vehicle);
    }

    private function buildStat(string $title, array $value, string $icon): ?Stat
    {
        if (empty($title) || empty($value)) {
            return null;
        }

        return Stat::make($title, $value['primary'] ?? '')
            ->icon($icon)
            ->description($value['secondary'] ?? '');
    }

    private function getMaintenanceStatus(Vehicle $vehicle): void
    {
        $maintenanceStatus = $vehicle->maintenance_status;

        if (empty($maintenanceStatus)) {
            return;
        }

        $value = [
            'primary' => str($maintenanceStatus['timeDiffHumans'])->ucfirst()->toString(),
            'secondary' => __('About :distance km', ['distance' => $maintenanceStatus['distance']]),
        ];

        if ($maintenanceStatus['daysTillDistanceDeadline'] < $maintenanceStatus['daysTillTimeDeadline']) {
            $value = [
                'primary' => __('About :distance km', ['distance' => $maintenanceStatus['distance']]),
                'secondary' => str($maintenanceStatus['timeDiffHumans'])->ucfirst()->toString(),
            ];
        }
        
        $this->statuses[] = [
            'title' => __('Maintenance'),
            'value' => $value,
            'icon' => 'mdi-car-wrench',
        ];
    }

    private function getApkStatus(Vehicle $vehicle): void
    {
        $apkStatus = $vehicle->apk_status;

        if (empty($apkStatus)) {
            return;
        }

        $this->statuses[] = [
            'title' => __('MOT'),
            'value' => [
                'primary' => str($apkStatus['timeDiffHumans'])->ucfirst(),
            ],
            'icon' => 'gmdi-security',
        ];
    }
}
