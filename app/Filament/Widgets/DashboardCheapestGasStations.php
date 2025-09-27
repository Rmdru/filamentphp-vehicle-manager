<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Refueling;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\HtmlString;

class DashboardCheapestGasStations extends BaseWidget
{
    protected static ?string $heading = '';

    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        $gasStationLogos = config('refuelings.gas_station_logos');
        $vehicle = Filament::getTenant();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        return $table
            ->query($this->getCheapestGasStations())
            ->emptyStateHeading(__('No refuelings found'))
            ->paginated(false)
            ->columns([
                TextColumn::make('gas_station')
                    ->hidden()
                    ->formatStateUsing(
                        function (Refueling $refueling) use ($gasStationLogos) {
                            $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

                            $logo = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];

                            return new HtmlString('<div class="w-5/12 min-h-16 max-h-40 flex items-center bg-white border border-gray-200 rounded p-2"><img class="max-h-40" src="' . $logo . '" /></div>');
                        }
                    ),
                TextColumn::make('gas_station')
                    ->label(__('Gas station')),
                TextColumn::make('visit_count')
                    ->label(__('Visit count')),
                TextColumn::make('avg_price')
                    ->label(__('Average price'))
                    ->money('EUR')
                    ->suffix('/' . $powertrain['unit_short'])
                    ->numeric(3)
                    ->prefix('€ '),
                TextColumn::make('lowest_price')
                    ->label(__('Lowest price'))
                    ->suffix('/' . $powertrain['unit_short'])
                    ->numeric(3)
                    ->prefix('€ '),
            ]);
    }

    private function getCheapestGasStations(): Builder
    {
        $gasStations = Refueling::query()
            ->where('vehicle_id', Filament::getTenant()->id)
            ->select(
                'id',
                'gas_station',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('AVG(unit_price) as avg_price'),
                DB::raw('MIN(unit_price) as lowest_price')
            )
            ->groupBy('gas_station')
            ->orderBy('avg_price', 'asc')
            ->orderBy('visit_count', 'desc')
            ->limit(5);

        return $gasStations;
    }
}
