<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Refueling;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DashboardFuelUsageByType extends BaseWidget
{
    protected static ?string $heading = '';

    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFuelUsageByType())
            ->emptyStateHeading(__('No refuelings found'))
            ->paginated(false)
            ->columns([
                TextColumn::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->formatStateUsing(fn (?string $state) => trans('fuel_types.' . $state) ?? $state),
                TextColumn::make('avg_consumption')
                    ->label(__('Average consumption'))
                    ->suffix($this->getConsumptionUnit())
                    ->formatStateUsing(fn (?float $state) => $state !== null ? number_format($state, 2, ',', '.') : '-'),
                TextColumn::make('total_amount')
                    ->label(__('Total refueled'))
                    ->suffix('l')
                    ->formatStateUsing(fn (?float $state) => $state !== null ? number_format($state, 2, ',', '.') : '-'),
                TextColumn::make('refueling_count')
                    ->label(__('Number of refuelings'))
                    ->formatStateUsing(fn (?int $state) => $state ?? 0),
            ]);
    }

    private function getFuelUsageByType(): Builder
    {
        $vehicleId = Filament::getTenant()->id;

        return Refueling::query()
            ->withTrashed()
            ->fromSub(function ($query) use ($vehicleId) {
                $query->selectRaw('fuel_type, amount, date, LEAD(fuel_consumption) OVER (ORDER BY date, id) as next_fuel_consumption')
                    ->from('refuelings')
                    ->where('vehicle_id', $vehicleId);
            }, 'refuelings_with_next')
            ->selectRaw('fuel_type as id, fuel_type, AVG(next_fuel_consumption) as avg_consumption, SUM(amount) as total_amount, COUNT(*) as refueling_count')
            ->whereNotNull('fuel_type')
            ->whereNotNull('next_fuel_consumption')
            ->groupBy('fuel_type')
            ->orderBy('fuel_type');
    }

    private function getConsumptionUnit(): string
    {
        $vehicle = Filament::getTenant();
        $powertrains = trans('powertrains');
        $powertrain = $powertrains[$vehicle->powertrain] ?? [];

        return $powertrain['consumption_unit'] ?? 'l/100km';
    }
}
