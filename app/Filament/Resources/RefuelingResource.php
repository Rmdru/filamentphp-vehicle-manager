<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefuelingResource\Pages;
use App\Filament\Resources\RefuelingResource\RelationManagers;
use App\HasVehicleName;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

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
        $brands = config('cars.brands');

        return $form
            ->schema([
                Fieldset::make(__('Refueling'))
                    ->schema([
                        Select::make('vehicle_id')
                            ->label(__('Vehicle'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->relationship('vehicle')
                            ->options(function (Vehicle $vehicle) use ($brands) {
                                $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();

                                $vehicles->car = $vehicles->map(function ($index) use ($brands) {
                                    return $index->car = $brands[$index->brand] . ' ' . $index->model . ' (' . $index->license_plate . ')';
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
                Fieldset::make(__('Fuel'))
                    ->schema([
                        Select::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->required()
                            ->native(false)
                            ->options(config('cars.fuel_types')),
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
                            ->step(0.01),
                        TextInput::make('total_price')
                            ->label(__('Total price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                        ]),
                Fieldset::make(__('Car'))
                    ->schema([
                        TextInput::make('mileage_begin')
                            ->label(__('Mileage begin'))
                            ->required()
                            ->suffix(' km')
                            ->numeric(),
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
                Fieldset::make(__('Circumstances'))
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
                                'auto' => __('Auto'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                            ])
                            ->icons([
                                'auto' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                            ])
                            ->colors([
                                'auto' => 'warning',
                                'airco' => 'info',
                                'heater' => 'primary',
                            ]),
                        Forms\Components\ToggleButtons::make('routes')
                            ->label(__('Routes'))
                            ->inline()
                            ->multiple()
                            ->grouped()
                            ->options([
                                'highway' => __('Highway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),
                            ])
                            ->icons([
                                'highway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',
                            ])
                            ->colors([
                                'highway' => 'info',
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
                                'slow' => 'success',
                                'average' => 'warning',
                                'fast' => 'primary',
                            ]),
                        Forms\Components\Textarea::make('comments')
                            ->label(__('Comments'))
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('cars.brands');
        $fuelTypes = config('cars.fuel_types');

        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                })->orderBy('date', 'desc');
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Stack::make([
                        TextColumn::make('date')
                            ->label(__('Car'))
                            ->icon(fn (Refueling $refueling) => 'si-' . strtolower(str_replace(' ', '', $brands[$refueling->vehicle->brand])))
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
                                'auto' => 'warning',
                                'airco' => 'info',
                                'heater' => 'primary',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'auto' => 'fas-temperature-high',
                                'airco' => 'mdi-air-conditioner',
                                'heater' => 'mdi-heat-wave',
                            })
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'auto' => __('Auto'),
                                'airco' => __('Airco'),
                                'heater' => __('Heater'),
                            }),
                        TextColumn::make('routes')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'highway' => 'info',
                                'country_road' => 'success',
                                'city' => 'warning',

                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'highway' => 'mdi-highway',
                                'country_road' => 'gmdi-landscape-s',
                                'city' => 'gmdi-location-city-r',

                            })
                            ->listWithLineBreaks()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'highway' => __('Highway'),
                                'country_road' => __('Country road'),
                                'city' => __('City'),

                            }),
                        TextColumn::make('driving_style')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'slow' => 'success',
                                'average' => 'warning',
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
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label(__('Vehicle'))
                    ->options(function(Vehicle $vehicle) {
                        $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();

                        $vehicles->car = $vehicles->map(function ($index) {
                            return $index->car = config('cars.brands')[$index->brand] . ' ' . $index->model . ' (' . $index->license_plate . ')';
                        });

                        return $vehicles->pluck('car', 'id');
                    })
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
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
