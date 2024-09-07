<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class Dashboard extends Page
{
    use HasFiltersAction;
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

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    Actions::make([
                        Actions\Action::make('resetFilters')
                            ->label(__('Reset filters'))
                            ->button()
                            ->action(function (Set $set) {
                                $this->filters = [];
                                $set('vehicleId', null);
                                $set('startDate', null);
                                $set('endDate', null);

                                Session::forget('Dashboard_filters');
                            })
                            ->color('gray'),
                    ]),
                    Select::make('vehicleId')
                        ->native(false)
                        ->label(__('Vehicle'))
                        ->options(function (Vehicle $vehicle) {
                            $vehicles = Vehicle::get();

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

    protected function getViewData(): array
    {
        $vehicle = Vehicle::selected()->latest()->first();
        $brands = config('cars.brands');
        $vehicle->brand = $brands[$vehicle->brand];

        return [
            'vehicle' => $vehicle,
        ];
    }
}
