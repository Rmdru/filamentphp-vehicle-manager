<?php

namespace App\Filament\Widgets;

use App\Models\EnvironmentalSticker;
use App\Models\Fine;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Parking;
use App\Models\Reconditioning;
use App\Models\Refueling;
use App\Models\Tax;
use App\Models\Toll;
use App\Models\Vehicle;
use App\Models\Vignette;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\EditAction;

class DashboardLatestCosts extends BaseWidget
{
    protected static ?string $heading = '';

    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getLatestCosts())
            ->emptyStateHeading(__('No costs this month'))
            ->paginated(false)
            ->columns([
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->default(__('None'))
                    ->formatStateUsing(fn ($state) => __($state)),
                TextColumn::make('item')
                    ->label(__('Item'))
                    ->words(10)
                    ->default(__('None'))
                    ->formatStateUsing(function ($state) {
                        $countries = config('countries');

                        if (array_key_exists($state, $countries)) {
                            return $countries[$state]['name'];
                        }

                        return __(ucfirst($state));
                    }),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('EUR')
                    ->default(__('None')),
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->default(__('None')),          
            ])
            ->actions([
                EditAction::make()
                    ->label(__('Show'))
                    ->icon('gmdi-remove-red-eye-r')
                    ->url(fn ($record) => strtolower($record['resource_name_plural'] . '/' . $record['id'] . '/edit')),
            ]);
    }

    protected function getLatestCosts(): Builder
    {
        $vehicle = Vehicle::selected()->first();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        $maintenancesThisMonth = Maintenance::select(['id', 'description as item', 'total_price as price', 'date'])
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->latest('date')
            ->addSelect(DB::raw('"Maintenance" as type'))
            ->addSelect(DB::raw('"maintenances" as resource_name_plural'));

        $refuelingsThisMonth = Refueling::selectRaw('id, CONCAT(amount, "' . $powertrain['unit_short'] . '") as item, total_price as price, date')
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->latest('date')
            ->addSelect(DB::raw('"Refueling" as type'))
            ->addSelect(DB::raw('"refuelings" as resource_name_plural'));

        $insurancesThisMonth = Insurance::selectRaw('id, type as item, price, DATE_FORMAT(CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE()), "-", invoice_day), "%Y-%m-%d") as date')
            ->where('vehicle_id', $vehicle->id)
            ->whereDate('start_date', '<', now())
            ->whereDate('end_date', '>', now())
            ->where('invoice_day', '<', Carbon::now()->dayOfMonth())
            ->addSelect(DB::raw('"Insurance" as type'))
            ->addSelect(DB::raw('"insurances" as resource_name_plural'))
            ->latest('date');

        $reconditioningsThisMonth = Reconditioning::selectRaw('id, REPLACE(type, "_", " ") AS item, price, date')
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->addSelect(DB::raw('"Reconditioning" as type'))
            ->addSelect(DB::raw('"reconditionings" as resource_name_plural'))
            ->latest('date');

        $taxesThisMonth = Tax::selectRaw('id, "" AS item, price, DATE_FORMAT(CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE()), "-", invoice_day), "%Y-%m-%d") as date')
            ->where('vehicle_id', $vehicle->id)
            ->whereDate('start_date', '<', now())
            ->whereDate('end_date', '>', now())
            ->where('invoice_day', '<', Carbon::now()->dayOfMonth())
            ->addSelect(DB::raw('"Road tax" as type'))
            ->addSelect(DB::raw('"taxes" as resource_name_plural'))
            ->latest('date');

        $parkingsThisMonth = Parking::selectRaw('id, location AS item, price, end_time as date')
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('end_time', Carbon::now()->month)
            ->whereYear('end_time', Carbon::now()->year)
            ->addSelect(DB::raw('"Parking" as type'))
            ->addSelect(DB::raw('"parkings" as resource_name_plural'))
            ->latest('end_time');

        $tollsThisMonth = Toll::selectRaw('id, CASE 
            WHEN end_location IS NOT NULL AND end_location != "" 
            THEN CONCAT(start_location, " - ", end_location) 
            ELSE start_location 
        END AS item, price, date')
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->addSelect(DB::raw('"Toll" as type'))
            ->addSelect(DB::raw('"tolls" as resource_name_plural'))
            ->latest('date');

        $finesThisMonth = Fine::select(['id', 'fact as item', 'price', 'date'])
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->latest('date')
            ->addSelect(DB::raw('"Fine" as type'))
            ->addSelect(DB::raw('"fines" as resource_name_plural'));

        $vignettesThisMonth = Vignette::select(['id', 'country as item', 'price', 'start_date as date'])
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('start_date', Carbon::now()->month)
            ->whereYear('start_date', Carbon::now()->year)
            ->latest('start_date')
            ->addSelect(DB::raw('"Vignette" as type'))
            ->addSelect(DB::raw('"vignettes" as resource_name_plural'));

        $environmentalStickersThisMonth = EnvironmentalSticker::select(['id', 'country as item', 'price', 'start_date as date'])
            ->where('vehicle_id', $vehicle->id)
            ->whereMonth('start_date', Carbon::now()->month)
            ->whereYear('start_date', Carbon::now()->year)
            ->latest('start_date')
            ->addSelect(DB::raw('"Environmental sticker" as type'))
            ->addSelect(DB::raw('"environmental-stickers" as resource_name_plural'));

        return $maintenancesThisMonth
            ->union($refuelingsThisMonth)
            ->union($insurancesThisMonth)
            ->union($reconditioningsThisMonth)
            ->union($taxesThisMonth)
            ->union($parkingsThisMonth)
            ->union($tollsThisMonth)
            ->union($finesThisMonth)
            ->union($vignettesThisMonth)
            ->union($environmentalStickersThisMonth)
            ->orderBy('date', 'desc');
    }
}
