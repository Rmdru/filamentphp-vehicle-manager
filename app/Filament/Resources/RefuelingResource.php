<?php

namespace App\Filament\Resources;

use App\Enums\RefuelingClimateControl;
use App\Enums\RefuelingDrivingStyle;
use App\Enums\RefuelingPaymentMethod;
use App\Enums\RefuelingRoutes;
use App\Enums\RefuelingTires;
use App\Filament\Resources\RefuelingResource\Pages;
use App\Models\Refueling;
use App\Models\Vehicle;
use App\Traits\CountryOptions;
use App\Traits\FuelTypeOptions;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
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
use Filament\Tables\Actions\ActionGroup;
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
use Livewire\Livewire;

class RefuelingResource extends Resource
{
    use FuelTypeOptions;
    use CountryOptions;

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
        $vehicle = Vehicle::selected()->first();
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

                                return new HtmlString('<div class="w-5/12 min-h-16 max-h-40 flex items-center bg-white rounded p-2"><img class="max-h-40" src="' . $logo . '" /></div>');
                            }
                    ),
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
                TextColumn::make('total_price')
                ->sortable()
                ->label(__('Total price'))
                ->icon('mdi-hand-coin-outline')
                ->money('EUR')
                ->summarize([
                    Average::make()->label(__('Total price average')),
                    Range::make()->label(__('Total price range')),
                ]),
                TextColumn::make('fuel_consumption')
                ->sortable()
                ->label(__('Fuel consumption'))
                ->icon(function (Refueling $refueling) {
                    $fuelConsumption = $refueling->fuel_consumption;
                    $avgFuelConsumption = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('fuel_consumption');

                    if ($fuelConsumption > $avgFuelConsumption) {
                        return 'gmdi-trending-up-r';
                    } else if ($fuelConsumption < $avgFuelConsumption) {
                        return 'gmdi-trending-down-r';
                    } else {
                        return 'mdi-approximately-equal';
                    }
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
                ])
                ])
            ->from('xl'),
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
                Tables\Actions\ViewAction::make(),
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
        $vehicle = Vehicle::selected()->first();
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
                                $vehicles = Vehicle::all();

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
                        Select::make('country')
                            ->label(__('Country'))
                            ->searchable()
                            ->native(false)
                            ->options((new self())->getCountryOptions()),
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
                            ->options((new self())->getFuelTypeOptions())
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('fuel_type', $state)),
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
                        TextInput::make('charge_time')
                            ->label(__('Charge time'))
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn($get) => in_array($get('fuel_type'), [
                                'Electricity >= 50 kW',
                                'Electricity < 50 kW',
                            ])),
                        Checkbox::make('service_by_attendant')
                            ->label(__('Service by attendant'))
                            ->visible(fn($get) => in_array($get('fuel_type'), [
                                'Premium Unleaded (E10)',
                                'Premium Unleaded (E5)',
                                'Super Plus 98',
                                'Super Plus 100',
                                'Super Plus 102',
                                'Diesel',
                                'Premium diesel',
                                'Adblue',
                                'LPG',
                                'CNG',
                                'E85',
                            ])),
                    ]),
                Fieldset::make('car')
                    ->label(__('Car'))
                    ->schema([
                        TextInput::make('mileage_begin')
                            ->label(__('Mileage begin'))
                            ->required()
                            ->suffix(' km')
                            ->numeric()
                            ->default(fn(Vehicle $vehicle) => $vehicle->selected()->first()->mileage_latest ?? null),
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
                            ->options(RefuelingTires::class),
                        ToggleButtons::make('climate_control')
                            ->label(__('Climate control'))
                            ->multiple()
                            ->inline()
                            ->options(RefuelingClimateControl::class),
                        ToggleButtons::make('routes')
                            ->label(__('Routes'))
                            ->inline()
                            ->multiple()
                            ->options(RefuelingRoutes::class),
                        ToggleButtons::make('driving_style')
                            ->label(__('Driving style'))
                            ->inline()
                            ->options(RefuelingDrivingStyle::class),
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
                            ->options(RefuelingPaymentMethod::class),
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
            'view' => Pages\ViewRefueling::route('/{record}'),
            'edit' => Pages\EditRefueling::route('/{record}/edit'),
        ];
    }
}
