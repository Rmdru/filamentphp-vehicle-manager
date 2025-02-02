<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefuelingResource\Pages;
use App\Models\Refueling;
use App\Models\Vehicle;
use App\Traits\FuelTypeOptions;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BuilderQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RefuelingResource extends Resource
{
    use FuelTypeOptions;

    protected static ?string $model = Refueling::class;

    protected static ?string $navigationIcon = 'gmdi-local-gas-station';

    public static function getNavigationLabel(): string
    {
        return __('Refuelings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Refuelings');
    }

    public static function getModelLabel(): string
    {
        return __('Refueling');
    }

    public static function table(Table $table): Table
    {
        $gasStationLogos = config('refuelings.gas_station_logos');
        $fuelTypes = trans('fuel_types');
        $vehicle = Vehicle::selected()->onlyDrivable()->first();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->select('*', DB::raw('mileage_end - mileage_begin as distance'))
                    ->whereHas('vehicle', function ($query) {
                        $query->selected();
                    });
            })
            ->columns([
                Split::make([
                    TextColumn::make('gas_station')
                        ->sortable()
                        ->formatStateUsing(
                            function (Refueling $refueling) use ($gasStationLogos) {
                                $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

                                $logo = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];

                                return new HtmlString('<div class="w-5/12 min-h-16 flex items-center bg-white rounded p-2"><img src="' . $logo . '" /></div>');
                            }
                        ),
                    Stack::make([
                        TextColumn::make('date')
                            ->sortable()
                            ->label(__('Date'))
                            ->date()
                            ->icon('gmdi-calendar-month-r'),
                        TextColumn::make('gas_station')
                            ->label(__('Gas station'))
                            ->sortable()
                            ->icon('gmdi-location-on-s')
                            ->searchable()
                            ->summarize(
                                Summarizer::make()
                                    ->label(__('Most visited gas station'))
                                    ->using(function (BuilderQuery $query): string {
                                        return $query->select('gas_station')
                                            ->selectRaw('COUNT(*) as count')
                                            ->groupBy('gas_station')
                                            ->orderByDesc('count')
                                            ->limit(1)
                                            ->pluck('gas_station')
                                            ->first();
                                    })
                            ),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('total_price')
                            ->sortable()
                            ->label(__('Total price'))
                            ->icon('mdi-hand-coin-outline')
                            ->money('EUR')
                            ->summarize([
                                Average::make()->label(__('Total price average')),
                                Range::make()->label(__('Total price range')),
                            ]),
                        TextColumn::make('unit_price')
                            ->sortable()
                            ->label(__('Unit price'))
                            ->icon('gmdi-local-offer')
                            ->prefix('€ ')
                            ->suffix('/' . $powertrain['unit_short'])
                            ->summarize([
                                Average::make()->label(__('Unit price average')),
                                Range::make()->label(__('Unit price range')),
                            ]),
                        TextColumn::make('costs_per_kilometer')
                            ->sortable()
                            ->label(__('Costs per kilometer'))
                            ->icon('uni-euro-circle-o')
                            ->money('EUR')
                            ->suffix('/km')
                            ->summarize([
                                Average::make()->label(__('Costs per kilomenter average')),
                                Range::make()->label(__('Costs per kilomenter range')),
                            ]),

                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('mileage_end')
                            ->sortable()
                            ->label(__('Mileage end'))
                            ->icon('gmdi-route')
                            ->suffix(' km'),
                        TextColumn::make('distance')
                            ->default('0')
                            ->sortable()
                            ->label(__('Distance'))
                            ->icon('gmdi-add')
                            ->suffix(' km'),
                        TextColumn::make('avg_speed')
                            ->sortable()
                            ->label(__('Average speed'))
                            ->icon('mdi-speedometer')
                            ->suffix(' km/h'),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('fuel_consumption')
                            ->sortable()
                            ->label(__('Fuel consumption'))
                            ->icon(function (Refueling $refueling) {
                                return 'mdi-engine';
                            })
                            ->badge()
                            ->color(function (Refueling $refueling) {
                                $fuelConsumption = $refueling->fuel_consumption;
                                $avgFuelConsumption = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('fuel_consumption');

                                if ($fuelConsumption > $avgFuelConsumption) {
                                    return 'danger';
                                } else if ($fuelConsumption < $avgFuelConsumption) {
                                    return 'success';
                                } else {
                                    return 'warning';
                                }
                            })
                            ->suffix($powertrain['consumption_unit'])
                            ->summarize([
                                Average::make()->label(__('Fuel consumption average')),
                                Range::make()->label(__('Fuel consumption range')),
                            ]),
                        TextColumn::make('amount')
                            ->sortable()
                            ->label(__('Amount'))
                            ->icon(function (Refueling $refueling) {
                                if (str($refueling->fuel_type)->contains('electric', true)) {
                                    return 'gmdi-battery-full-r';
                                }

                                return 'mdi-fuel';
                            })
                            ->suffix($powertrain['unit_short'])
                            ->summarize([
                                Average::make()->label(__('Amount average')),
                                Range::make()->label(__('Amount range')),
                            ]),
                        TextColumn::make('fuel_type')
                            ->sortable()
                            ->label(__('Fuel type'))
                            ->icon(function (Refueling $refueling) {
                                if (str($refueling->fuel_type)->contains('electric', true)) {
                                    return 'fas-charging-station';
                                }

                                return 'gmdi-local-gas-station-r';
                            })
                            ->formatStateUsing(fn(Refueling $refueling) => $fuelTypes[$refueling->fuel_type]),
                        TextColumn::make('fuel_consumption_onboard_computer')
                            ->sortable()
                            ->label(__('Fuel consumption onboard computer'))
                            ->icon('gmdi-dashboard-r')
                            ->suffix(' l/100km'),
                    ])
                        ->space(),
                ]),
                Panel::make([
                    Split::make([
                        TextColumn::make('tires')
                            ->sortable()
                            ->badge()
                            ->label(__('Tires'))
                            ->color(fn(string $state): string => match ($state) {
                                'all_season' => 'danger',
                                'summer' => 'warning',
                                'winter' => 'info',
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'all_season' => 'gmdi-sunny-snowing',
                                'summer' => 'gmdi-wb-sunny-o',
                                'winter' => 'forkawesome-snowflake-o',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'all_season' => __('All season tires'),
                                'summer' => __('Summer tires'),
                                'winter' => __('Winter tires'),
                            }),
                        TextColumn::make('climate_control')
                            ->sortable()
                            ->badge()
                            ->label(__('Climate control'))
                            ->color(fn(string $state): string => match ($state) {
                                'automatically' => 'warning',
                                'airco' => 'info',
                                'heater' => 'danger',
                                'demisting' => 'success',
                                'defrost' => 'info',
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'automatically' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                                'demisting' => 'mdi-wiper',
                                'defrost' => 'forkawesome-snowflake-o',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'automatically' => __('Automatically'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                                'demisting' => __('Demisting'),
                                'defrost' => __('Defrost'),
                            }),
                        TextColumn::make('routes')
                            ->sortable()
                            ->badge()
                            ->label(__('Routes'))
                            ->color(fn(string $state): string => match ($state) {
                                'motorway' => 'info',
                                'country_road' => 'success',
                                'city' => 'warning',
                                'trailer' => 'danger',
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'motorway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',
                                'trailer' => 'mdi-truck-trailer',
                            })
                            ->listWithLineBreaks()
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'motorway' => __('Motorway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),
                                'trailer' => __('Trailer'),
                            }),
                        TextColumn::make('driving_style')
                            ->sortable()
                            ->badge()
                            ->label(__('Driving style'))
                            ->color(fn(string $state): string => match ($state) {
                                'slow' => 'warning',
                                'average' => 'success',
                                'fast' => 'primary',
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'slow' => 'mdi-speedometer-slow',
                                'average' => 'mdi-speedometer-medium',
                                'fast' => 'mdi-speedometer',
                            })
                            ->listWithLineBreaks()
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'slow' => __('Slow'),
                                'average' => __('Average'),
                                'fast' => __('Fast'),
                            }),
                        TextColumn::make('payment_method')
                            ->label(__('Payment method'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state) => match ($state) {
                                'cash' => 'mdi-hand-coin-outline',
                                'bank_card' => 'gmdi-credit-card',
                                'loyalty_program' => 'mdi-gift',
                                'fuel_card' => 'gmdi-local-gas-station-r',
                                'app' => 'mdi-cellphone-wireless',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'cash' => __('Cash'),
                                'bank_card' => __('Bank card'),
                                'loyalty_program' => __('Loyality program'),
                                'fuel_card' => __('Fuel card'),
                                'app' => __('App'),
                            }),
                        TextColumn::make('discount')
                            ->label(__('Discount')),
                    ]),
                ])
                    ->collapsible(),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('Date from'))
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label(__('Date until'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['date_from'] && $data['date_until']) {
                            $indicators['date'] = __('Date from :from until :until', [
                                'from' => Carbon::parse($data['date_from'])->isoFormat('MMM D, Y'),
                                'until' => Carbon::parse($data['date_until'])->isoFormat('MMM D, Y'),
                            ]);
                        } else if ($data['date_from']) {
                            $indicators['date'] = __('Date from :from', [
                                'from' => Carbon::parse($data['date_from'])->isoFormat('MMM D, Y'),
                            ]);
                        } else if ($data['date_until']) {
                            $indicators['date'] = __('Date until :until', [
                                'until' => Carbon::parse($data['date_until'])->isoFormat('MMM D, Y'),
                            ]);
                        }

                        return $indicators;
                    }),
                SelectFilter::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->options($fuelTypes),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function form(Form $form): Form
    {
        $vehicle = Vehicle::selected()->onlyDrivable()->first();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        return $form
            ->schema([
                Fieldset::make('Refueling')
                    ->label(__('Refueling'))
                    ->schema([
                        Select::make('vehicle_id')
                            ->disabled()
                            ->label(__('Vehicle'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->relationship('vehicle')
                            ->default($vehicle->id ?? null)
                            ->options(function (Vehicle $vehicle) {
                                $vehicles = Vehicle::onlyDrivable()->get();

                                $vehicles->car = $vehicles->map(function ($index) {
                                    return $index->full_name_with_license_plate;
                                });

                                return $vehicles->pluck('full_name_with_license_plate', 'id');
                            }),
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        TextInput::make('gas_station')
                            ->label(__('Gas station'))
                            ->required()
                            ->maxLength(100)
                            ->helperText(__('The first word is used for the brand logo')),
                    ]),
                Fieldset::make('fuel')
                    ->label(__('Fuel'))
                    ->schema([
                        Select::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->required()
                            ->native(false)
                            ->options((new self())->getFuelTypeOptions()),
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->required()
                            ->suffix($powertrain['unit_short'])
                            ->step(0.01),
                        TextInput::make('unit_price')
                            ->label(__('Unit price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input, ' . ', ', ', 3)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->suffix('/' . $powertrain['unit_short'])
                            ->step(0.001),
                        TextInput::make('total_price')
                            ->label(__('Total price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                    ]),
                Fieldset::make('car')
                    ->label(__('Car'))
                    ->schema([
                        TextInput::make('mileage_begin')
                            ->label(__('Mileage begin'))
                            ->required()
                            ->suffix(' km')
                            ->numeric()
                            ->default(fn(Vehicle $vehicle) => $vehicle->selected()->onlyDrivable()->first()->mileage_latest ?? null),
                        TextInput::make('mileage_end')
                            ->label(__('Mileage end'))
                            ->required()
                            ->suffix(' km')
                            ->numeric(),
                        TextInput::make('fuel_consumption_onboard_computer')
                            ->label(__('Fuel consumption onboard computer'))
                            ->suffix($powertrain['consumption_unit'])
                            ->numeric(),
                    ]),
                Fieldset::make('circumstances')
                    ->label(__('Circumstances'))
                    ->schema([
                        ToggleButtons::make('tires')
                            ->label(__('Tires'))
                            ->inline()
                            ->grouped()
                            ->options([
                                'all_season' => __('All season'),
                                'summer' => __('Summer'),
                                'winter' => __('Winter'),
                            ])
                            ->icons([
                                'all_season' => 'gmdi-sunny-snowing',
                                'summer' => 'gmdi-wb-sunny-o',
                                'winter' => 'forkawesome-snowflake-o',
                            ])
                            ->colors([
                                'all_season' => 'danger',
                                'summer' => 'warning',
                                'winter' => 'info',
                            ]),
                        ToggleButtons::make('climate_control')
                            ->label(__('Climate control'))
                            ->multiple()
                            ->inline()
                            ->options([
                                'automatically' => __('Automatically'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                                'demisting' => __('Demisting'),
                                'defrost' => __('Defrost'),
                            ])
                            ->icons([
                                'automatically' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                                'demisting' => 'mdi-wiper',
                                'defrost' => 'forkawesome-snowflake-o',
                            ])
                            ->colors([
                                'automatically' => 'warning',
                                'airco' => 'info',
                                'heater' => 'danger',
                                'demisting' => 'success',
                                'defrost' => 'info',
                            ]),
                        ToggleButtons::make('routes')
                            ->label(__('Routes'))
                            ->inline()
                            ->multiple()
                            ->options([
                                'motorway' => __('Motorway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),
                                'trailer' => __('Trailer'),
                            ])
                            ->icons([
                                'motorway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',
                                'trailer' => 'mdi-truck-trailer',
                            ])
                            ->colors([
                                'motorway' => 'info',
                                'country_road' => 'success',
                                'city' => 'warning',
                                'trailer' => 'danger',
                            ]),
                        ToggleButtons::make('driving_style')
                            ->label(__('Driving style'))
                            ->inline()
                            ->grouped()
                            ->options([
                                'slow' => __('Slow'),
                                'average' => __('Average'),
                                'fast' => __('Fast'),
                            ])
                            ->icons([
                                'slow' => 'mdi-speedometer-slow',
                                'average' => 'mdi-speedometer-medium',
                                'fast' => 'mdi-speedometer',
                            ])
                            ->colors([
                                'slow' => 'warning',
                                'average' => 'success',
                                'fast' => 'primary',
                            ]),
                        TextInput::make('avg_speed')
                            ->label(__('Average speed'))
                            ->numeric()
                            ->suffix('km/h'),
                        Textarea::make('comments')
                            ->label(__('Comments')),
                    ]),
                Fieldset::make('payment')
                    ->label(__('Payment'))
                    ->schema([
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->icons([
                                'cash' => 'mdi-hand-coin-outline',
                                'bank_card' => 'gmdi-credit-card',
                                'loyalty_program' => 'mdi-gift',
                                'fuel_card' => 'gmdi-local-gas-station-r',
                                'app' => 'mdi-cellphone-wireless',
                            ])
                            ->options([
                                'cash' => __('Cash'),
                                'bank_card' => __('Bank card'),
                                'loyalty_program' => __('Loyality program'),
                                'fuel_card' => __('Fuel card'),
                                'app' => __('App'),
                            ]),
                        TextInput::make('discount')
                            ->label(__('Discount')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefuelings::route('/'),
            'create' => Pages\CreateRefueling::route('/create'),
            'edit' => Pages\EditRefueling::route('/{record}/edit'),
        ];
    }
}
