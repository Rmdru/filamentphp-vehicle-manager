<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefuelingResource\Pages;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RefuelingResource extends Resource
{
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Refueling')
                    ->label(__('Refueling'))
                    ->schema([
                        Select::make('vehicle_id')
                            ->label(__('Vehicle'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->relationship('vehicle')
                            ->default(fn (Vehicle $vehicle) => $vehicle->selected()->latest()->first()->id)
                            ->options(function (Vehicle $vehicle) {
                                $vehicles = Vehicle::get();

                                $vehicles->car = $vehicles->map(function ($index) {
                                    return $index->car = $index->full_name . ' (' . $index->license_plate . ')';
                                });

                                return $vehicles->pluck('car', 'id');
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
                            ->maxLength(100),
                        ]),
                Fieldset::make('fuel')
                    ->label(__('Fuel'))
                    ->schema([
                        Select::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->required()
                            ->native(false)
                            ->options(trans('fuel_types')),
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->required()
                            ->suffix('l or kWh')
                            ->step(0.01),
                        TextInput::make('unit_price')
                            ->label(__('Unit price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
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
                            ->default(fn (Vehicle $vehicle) => $vehicle->selected()->latest()->first()->mileage_latest),
                        TextInput::make('mileage_end')
                            ->label(__('Mileage end'))
                            ->required()
                            ->suffix(' km')
                            ->numeric(),
                        TextInput::make('fuel_consumption_onboard_computer')
                            ->label(__('Fuel consumption onboard computer'))
                            ->suffix(' l/100km')
                            ->numeric(),
                        ]),
                Fieldset::make('circumstances')
                    ->label(__('Circumstances'))
                    ->schema([
                        Forms\Components\ToggleButtons::make('tyres')
                            ->label(__('Tyres'))
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
                        Forms\Components\ToggleButtons::make('climate_control')
                            ->label(__('Climate control'))
                            ->multiple()
                            ->inline()
                            ->grouped()
                            ->options([
                                'automatically' => __('Automatically'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                            ])
                            ->icons([
                                'automatically' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                            ])
                            ->colors([
                                'automatically' => 'warning',
                                'airco' => 'info',
                                'heater' => 'primary',
                            ]),
                        Forms\Components\ToggleButtons::make('routes')
                            ->label(__('Routes'))
                            ->inline()
                            ->multiple()
                            ->grouped()
                            ->options([
                                'motorway' => __('Motorway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),
                            ])
                            ->icons([
                                'motorway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',
                            ])
                            ->colors([
                                'motorway' => 'info',
                                'country_road' => 'success',
                                'city' => 'warning',
                            ]),
                        Forms\Components\ToggleButtons::make('driving_style')
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
                        Forms\Components\Textarea::make('comments')
                            ->label(__('Comments'))
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('vehicles.brands');
        $gasStationLogos = config('refuelings.gas_station_logos');
        $fuelTypes = trans('fuel_types');

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                })->latest();
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('gas_station')
                        ->formatStateUsing(
                            function (Refueling $refueling) use ($gasStationLogos) {
                                $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

                                $logo = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];

                                return new HtmlString('<div class="p-3 rounded-full bg-white w-5/12 min-h-16 flex items-center"><img src="' . $logo . '" /></div>');
                            }
                        ),
                    Stack::make([
                        TextColumn::make('date')
                            ->label(__('Vehicle'))
                            ->icon(fn (Refueling $refueling) => 'si-' . str($brands[$refueling->vehicle->brand])->replace(' ', '')->lower())
                            ->formatStateUsing(fn (Refueling $refueling) => $brands[$refueling->vehicle->brand] . " " . $refueling->vehicle->model),
                        TextColumn::make('date')
                            ->label(__('Date'))
                            ->date()
                            ->icon('gmdi-calendar-month-r'),
                        TextColumn::make('gas_station')
                            ->label(__('Gas station'))
                            ->icon('gmdi-location-on-s'),

                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('total_price')
                            ->label(__('Total price'))
                            ->icon('mdi-hand-coin-outline')
                            ->money('EUR'),
                        TextColumn::make('unit_price')
                            ->label(__('Unit price'))
                            ->icon('gmdi-local-offer')
                            ->money('EUR')
                            ->suffix('/l'),
                        TextColumn::make('costs_per_kilometer')
                            ->label(__('Costs per kilometer'))
                            ->icon('uni-euro-circle-o')
                            ->money('EUR')
                            ->suffix('/km'),

                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('mileage_end')
                            ->label(__('Mileage end'))
                            ->icon('gmdi-route')
                            ->suffix(' km'),
                        TextColumn::make('distance')
                            ->default('0')
                            ->label(__('Distance'))
                            ->icon('gmdi-add')
                            ->suffix(' km')
                            ->formatStateUsing(fn (Refueling $refueling) => $refueling->mileage_end - $refueling->mileage_begin),
                        TextColumn::make('avg_speed')
                            ->label(__('Average speed'))
                            ->icon('mdi-speedometer')
                            ->suffix(' km/h'),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('fuel_consumption')
                            ->label(__('Fuel consumption'))
                            ->icon('gmdi-local-gas-station-r')
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
                            ->suffix(' l/100km'),
                        TextColumn::make('amount')
                            ->label(__('Amount'))
                            ->icon('gmdi-water-drop-r')
                            ->suffix(' l'),
                        TextColumn::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->icon('gmdi-local-gas-station-r')
                            ->formatStateUsing(fn (Refueling $refueling) => $fuelTypes[$refueling->fuel_type]),
                        TextColumn::make('fuel_consumption_onboard_computer')
                            ->label(__('Fuel consumption onboard computer'))
                            ->icon('gmdi-dashboard-r')
                            ->suffix(' l/100km'),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('tyres')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'all_season' => 'danger',
                                'summer' => 'warning',
                                'winter' => 'info',
                                default => '',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'all_season' => 'gmdi-sunny-snowing',
                                'summer' => 'gmdi-wb-sunny-o',
                                'winter' => 'forkawesome-snowflake-o',
                                default => '',
                            })
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'all_season' => __('All season tyres'),
                                'summer' => __('Summer tyres'),
                                'winter' => __('Winter tyres'),
                            }),
                        TextColumn::make('climate_control')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'automatically' => 'warning',
                                'airco' => 'info',
                                'heater' => 'primary',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'automatically' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                            })
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'automatically' => __('Automatically'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                            }),
                        TextColumn::make('routes')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'motorway' => 'info',
                                'country_road' => 'success',
                                'city' => 'warning',

                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'motorway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',

                            })
                            ->listWithLineBreaks()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'motorway' => __('Motorway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),

                            }),
                        TextColumn::make('driving_style')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'slow' => 'warning',
                                'average' => 'success',
                                'fast' => 'primary',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'slow' => 'mdi-speedometer-slow',
                                'average' => 'mdi-speedometer-medium',
                                'fast' => 'mdi-speedometer',
                            })
                            ->listWithLineBreaks()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'slow' => __('Slow'),
                                'average' => __('Average'),
                                'fast' => __('Fast'),
                            }),
                    ])
                        ->space(1),
                ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
