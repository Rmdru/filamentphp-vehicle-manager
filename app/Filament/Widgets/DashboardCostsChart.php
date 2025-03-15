<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Traits\GenerateRandomHexColor;
use App\Traits\IsMobile;
use Filament\Widgets\ChartWidget;

class DashboardCostsChart extends ChartWidget
{
    use GenerateRandomHexColor;
    use IsMobile;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $vehicle = Vehicle::selected()->first();
        $costData = $vehicle->calculateMonthlyCosts(now()->startOfYear(), now()->endOfYear());

        $datasets = [];

        foreach ($costData['monthlyCosts'] as $costs) {
            foreach ($costs as $label => $cost) {
                $datasets[$label]['label'] = __($label);
                $datasets[$label]['data'][] = $cost;
                $datasets[$label]['backgroundColor'] = $this->generateRandomHexColor();
            }
        }

        return [
            'datasets' => array_values($datasets),
            'labels' => $costData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 1/2,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ];
    }
}
