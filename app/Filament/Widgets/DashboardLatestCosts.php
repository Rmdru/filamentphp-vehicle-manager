<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\Cost;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;

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
                IconColumn::make('icon')
                    ->label('')
                    ->icon(fn ($state) => $state),
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
                    ->url(fn ($record) => strtolower($record['link'] . '/' . $record['id'] . '/edit')),
            ]);
    }

    private function getLatestCosts(): Builder
    {
        $vehicleId = Filament::getTenant()->id;

        $queries = [];
        foreach (Cost::types() as $type => $config) {
            $queries[] = $this->getCostsForType($type, $config, $vehicleId);
        }

        $unionQuery = array_shift($queries);
        foreach ($queries as $query) {
            $unionQuery = $unionQuery->union($query);
        }

        return $unionQuery->orderBy('date', 'desc');
    }

    private function getCostsForType(string $type, array $config, string $vehicleId): Builder
    {
        $model = $config['model'];
        $dateColumn = $config['dateColumn'];
        $priceField = $config['priceField'];
        $itemField = $config['itemField'] ?? '""';
        $link = $config['link'] ?? '';
        $invoiceDates = $config['invoiceDates'] ?? '';
        $monthly = $config['monthly'] ?? false;
        $icon = $config['icon'] ?? false;

        $dateSelect = ! empty($invoiceDates) ? $invoiceDates : $dateColumn;

        return $model::selectRaw("id, $itemField as item, $priceField as price, $dateSelect as date")
            ->where('vehicle_id', $vehicleId)
            ->when($monthly, function ($query) {
                return $query->whereDate('start_date', '<', now())
                    ->whereDate('end_date', '>', now())
                    ->where('invoice_day', '<', Carbon::now()->dayOfMonth())
                    ->latest('date');
            })
            ->when(! $monthly, function ($query) use ($dateColumn) {
                return $query->whereMonth($dateColumn, Carbon::now()->month)
                    ->whereYear($dateColumn, Carbon::now()->year)
                    ->latest($dateColumn);
            })
            ->addSelect(DB::raw('"' . $type . '" as type'))
            ->addSelect(DB::raw('"' . $link . '" as link'))
            ->addSelect(DB::raw('"' . $icon . '" as icon'));
    }
}
