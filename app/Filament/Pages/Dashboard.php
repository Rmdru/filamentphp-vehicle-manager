<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DashboardResource\Widgets\DashboardOverview;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersAction;

    protected static ?string $navigationIcon = 'gmdi-bar-chart-r';

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }
    public static function getModelLabel(): string
    {
        return __('Dashboard');
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    Select::make('vehicle_id')
                        ->label(__('Vehicle'))
                        ->options(function(Vehicle $vehicle) {
                            $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();

                            $vehicles->car = $vehicles->map(function ($index) {
                                return $index->car = config('cars.brands')[$index->brand] . ' ' . $index->model . ' (' . $index->license_plate . ')';
                            });

                            return $vehicles->pluck('car', 'id');
                        }),
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate'),
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardOverview::class,
        ];
    }
}
