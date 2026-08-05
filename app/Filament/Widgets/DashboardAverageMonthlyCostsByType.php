<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\Cost;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class DashboardAverageMonthlyCostsByType extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-average-monthly-costs-by-type';

    protected static ?string $pollingInterval = null;

    protected function getViewData(): array
    {
        $vehicle = Filament::getTenant();
        $start = request()->query('dashboard_start', '');
        $end = request()->query('dashboard_end', '');

        $averages = $vehicle->calculateAverageMonthlyCostsByType($start, $end);
        $costDefinitions = Cost::types($vehicle);

        return [
            'costs' => collect($averages)
                ->map(function (float $amount, string $label) use ($costDefinitions) {
                    return [
                        'label' => __($label),
                        'amount' => number_format($amount, 2, ',', '.'),
                        'icon' => $costDefinitions[$label]['icon'] ?? 'mdi-help-circle-outline',
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
