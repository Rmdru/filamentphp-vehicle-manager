<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\FuelPrice;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DashboardFuelPricesAbroad extends Widget
{
    protected static string $view = 'filament.widgets.fuel-prices-abroad';

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

        asort($fuelTypesSorted);

        $pivoted = [];

        foreach ($fuelPrices as $fuelPrice) {
            $pivoted[$fuelPrice->country][$fuelPrice->fuel_type] = [
                'price' => str($fuelPrice->price)->replace('.', ','),
                'max_detour_only_fuel_costs' => $fuelPrice->max_detour_only_fuel_costs ?? 0,
                'max_detour_all_costs' => $fuelPrice->max_detour_all_costs ?? 0,
            ];
        }

        return [
            'fuelPrices' => $pivoted,
            'fuelTypes' => $fuelTypesSorted,
        ];
    }
}
