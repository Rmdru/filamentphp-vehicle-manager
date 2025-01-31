<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Set;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
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

    public function getTitle(): string|Htmlable
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
                                $set('startDate', null);
                                $set('endDate', null);

                                Session::forget('Dashboard_filters');
                            })
                            ->color('gray'),
                    ]),
                    DatePicker::make('startDate')
                        ->label(__('Start date'))
                        ->native(false)
                        ->maxDate(now()),
                    DatePicker::make('endDate')
                        ->label(__('End date'))
                        ->native(false)
                        ->maxDate(now()),
                ]),
        ];
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
