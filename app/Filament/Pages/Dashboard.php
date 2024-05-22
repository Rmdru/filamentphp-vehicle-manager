<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DashboardResource\Widgets\DashboardOverview;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Illuminate\Support\Facades\Auth;

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
                    Select::make('vehicleId')
                        ->native(false)
                        ->label(__('Vehicle'))
                        ->options(function(Vehicle $vehicle) {
                            $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();

                            $vehicles->car = $vehicles->map(function ($index) {
                                return $index->car = config('cars.brands')[$index->brand] . ' ' . $index->model . ' (' . $index->license_plate . ')';
                            });

                            return $vehicles->pluck('car', 'id');
                        }),
                    DatePicker::make('startDate')
                        ->native(false)
                        ->maxDate(now()),
                    DatePicker::make('endDate')
                        ->native(false)
                        ->maxDate(now()),
                ])
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardOverview::make([
                'filters' => $this->filters,
            ]),
        ];
    }
}
