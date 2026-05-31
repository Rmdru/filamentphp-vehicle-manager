<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\FuelPrice;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DashboardFuelPricesAbroad extends Widget
{
    protected static string $view = 'filament.widgets.fuel-prices-abroad';

    public function refreshFuelPrices(): void
    {
        Artisan::call('import:fuel-prices');

        Notification::make()
            ->title(__('Fuel prices imported successfully.'))
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $vehicleId = Filament::getTenant()->id;

        $latestDates = FuelPrice::query()
            ->select('country', DB::raw('MAX(date) as latest_date'))
            ->groupBy('country');

        $fuelPrices = FuelPrice::query()
            ->joinSub($latestDates, 'latest', function ($join) {
                $join->on('fuel_prices.country', '=', 'latest.country')
                    ->on('fuel_prices.date', '=', 'latest.latest_date');
            })
            ->leftJoin('fuel_detour_aggregates', function($join) use ($vehicleId) {
                $join->on('fuel_detour_aggregates.fuel_price_id', '=', 'fuel_prices.id')
                    ->where('fuel_detour_aggregates.vehicle_id', $vehicleId);
            })
            ->select([
                'fuel_prices.country',
                'fuel_prices.date',
                'fuel_prices.fuel_type',
                'fuel_prices.price',
                'fuel_detour_aggregates.max_detour_only_fuel_costs',
                'fuel_detour_aggregates.max_detour_all_costs'
            ])
            ->get();

        $fuelTypes = $fuelPrices->pluck('fuel_type')->unique()->sort()->values();
        $fuelTypesSorted = [];

        foreach ($fuelTypes as $fuelType) {
            $fuelTypesSorted[$fuelType] = trans('fuel_types.' . $fuelType);
        }

        $pivoted = [];

        foreach ($fuelPrices as $fuelPrice) {
            $pivoted[$fuelPrice->country][$fuelPrice->fuel_type] = [
                'date' => $fuelPrice->date->format('d-m-Y'),
                'price' => str($fuelPrice->price)->replace('.', ','),
                'max_detour_only_fuel_costs' => $fuelPrice->max_detour_only_fuel_costs ?? 0,
                'max_detour_all_costs' => $fuelPrice->max_detour_all_costs ?? 0,
            ];
        }

        asort($fuelTypesSorted);
        ksort($pivoted);

        return [
            'fuelPrices' => $pivoted,
            'fuelTypes' => $fuelTypesSorted,
        ];
    }
}
