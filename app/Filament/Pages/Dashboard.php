<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    use InteractsWithRecord;

    protected static ?string $navigationIcon = 'gmdi-bar-chart-r';

    protected static string $view = 'filament.pages.dashboard';

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public static function getModelLabel(): string
    {
        return __('Dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Dashboard');
    }

    protected function getViewData(): array
    {
        $vehicle = Vehicle::selected()->first();
        $brands = config('vehicles.brands');

        $vehicle->brand = $brands[$vehicle->brand];

        return [
            'vehicle' => $vehicle,
        ];
    }
}
