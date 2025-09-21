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
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
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
    use CountryOptions;
    use IsMobile;

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
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Refuelings'))
                    ->modalContent(new HtmlString(__('Here you can add your refuelings and charge sessions to keep track of the costs and fuel consumption. This category only includes costs of fuel and energy to move and use the vehicle. The prices and quantities entered are the value stated on the receipt or invoice.')))
                    ->modalIcon('gmdi-local-gas-station')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
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

                                return new HtmlString('<div class="w-5/12 min-h-16 max-h-40 flex items-center bg-white border border-gray-200 rounded p-2"><img class="max-h-40" src="' . $logo . '" /></div>');
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
                                ->hidden((new self)->isMobile())
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
                            }

                            return 'mdi-approximately-equal';
                        })
                        ->badge()
                        ->color(function (Refueling $refueling) {
                            $fuelConsumption = $refueling->fuel_consumption;
                            $avgFuelConsumption = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('fuel_consumption');

                            if ($fuelConsumption > $avgFuelConsumption) {
                                return 'danger';
                            } else if ($fuelConsumption < $avgFuelConsumption) {
                                return 'success';
                            }

                            return 'warning';
                        })
                        ->suffix($powertrain['consumption_unit'])
                        ->summarize([
                            Average::make()->label(__('Fuel consumption average'))->hidden((new self)->isMobile()),
                            Range::make()->label(__('Fuel consumption range'))->hidden((new self)->isMobile()),
                        ]),
                    TextColumn::make('fuel_type')
                        ->sortable()
                        ->badge()
                        ->label(__('Fuel type'))
                        ->icon('gmdi-local-gas-station-r')
                        ->formatStateUsing(fn(string $state) => trans('fuel_types')[$state]),
                ])
                    ->from('xl'),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('Date from'))
                            ->native((new self)->isMobile()),
                        DatePicker::make('date_until')
                            ->label(__('Date until'))
                            ->native((new self)->isMobile()),
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
                ActionGroup::make([
                    EditAction::make(),
                    ReplicateAction::make()
                        ->label(__('Duplicate'))
                        ->icon('gmdi-file-copy-r')
                        ->requiresConfirmation()
                        ->excludeAttributes(['distance'])
                ]),
                ViewAction::make()
                    ->iconButton()
                    ->icon('gmdi-keyboard-arrow-right-r')
                    ->color('primary'),
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
        $previousRefueling = Refueling::query()
            ->where('vehicle_id', $vehicle->id)
            ->latest()
            ->first();
        
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
                            ->native((new self)->isMobile())
                            ->relationship('vehicle')
                            ->default($vehicle->id ?? null)
                            ->options(function () {
                                $vehicles = Vehicle::all();

                                $vehicles->car = $vehicles->map(function ($index) {
                                    return $index->full_name_with_license_plate;
                                });

                                return $vehicles->pluck('full_name_with_license_plate', 'id');
                            }),
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native((new self)->isMobile())
                            ->displayFormat('d-m-Y')
                            ->default(now())
                            ->maxDate(now()),
                        Select::make('country')
                            ->label(__('Country'))
                            ->searchable()
                            ->native((new self)->isMobile())
                            ->options((new self())->getCountryOptions())
                            ->default($vehicle->country_registration),
                        TextInput::make('gas_station')
                            ->label(__('Gas station'))
                            ->required()
                            ->maxLength(100)
                            ->helperText(__('The first word is used for the brand logo (if available)')),
                    ]),
                Fieldset::make('fuel')
                    ->label(__('Fuel'))
                    ->schema([
                        Select::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->required()
                            ->native((new self)->isMobile())
                            ->options((new self())->getFuelTypeOptions())
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('fuel_type', $state)),
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->required()
                            ->suffix($powertrain['unit_short'])
                            ->step(0.01)
                            ->lazy()
                            ->afterStateUpdated(function ($set, $state, $get) {
                                rescue(function () use ($set, $state, $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $unitPrice = $get('unit_price') ?? 0;

                                    $totalPrice = $state * $unitPrice;

                                    if (! empty($totalPrice) && $totalPrice > 0) {
                                        $set('total_price', $totalPrice);
                                    }
                                });
                            }),
                        TextInput::make('percentage')
                            ->label(__('Tank percentage after refueling'))
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->suffix('%')
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText(__('Default value is 100%')),
                        TextInput::make('unit_price')
                            ->label(__('Unit price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input, \'.\', \' \', 3)'))
                            ->required()
                            ->prefix('€')
                            ->suffix('/' . $powertrain['unit_short'])
                            ->step(0.001)
                            ->lazy()
                            ->afterStateUpdated(function ($set, $state, $get) {
                                rescue(function () use ($set, $state, $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $amount = $get('amount') ?? 0;
                                    $totalPrice = $get('total_price') ?? 0;

                                    $totalPrice = $amount * $state;

                                    if (! empty($totalPrice) && $totalPrice > 0) {
                                        $set('total_price', $totalPrice);
                                    }
                                });
                            }),
                        TextInput::make('total_price')
                            ->label(__('Total price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input, \'.\', \' \',)'))
                            ->required()
                            ->prefix('€')
                            ->step(0.01)
                            ->lazy()
                            ->afterStateUpdated(function ($set, $state, $get) {
                                rescue(function () use ($set, $state, $get) {
                                    if (
                                        empty($state)
                                        || (
                                            ! empty($get('amount'))
                                            && ! empty($get('unit_price'))
                                        )
                                    ) {
                                        return;
                                    }

                                    $unitPrice = $get('unit_price') ?? 0;
                                    $totalPrice = $get('total_price') ?? 0;

                                    $amount = $totalPrice / $unitPrice;

                                    if (! empty($amount) && $amount > 0) {
                                        $set('amount', $amount);
                                    }
                                });
                            }),
                        TimePicker::make('charge_time')
                            ->label(__('Charge time'))
                            ->native((new self)->isMobile())
                            ->visible(fn($get) => in_array($get('fuel_type'), [
                                'Electricity DC',
                                'Electricity AC',
                            ]))
                            ->reactive(),
                        Checkbox::make('service_by_attendant')
                            ->label(__('Service by attendant'))
                            ->visible(fn($get) => in_array($get('fuel_type'), [
                                'Unleaded 95 (E10)',
                                'Unleaded 95 (E5)',
                                'Super Plus',
                                'V-Power 100',
                                'Ultimate 102',
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
                            ->default(fn(Vehicle $vehicle) => $vehicle->selected()->first()->mileage_latest ?? null)
                            ->lazy()
                            ->afterStateUpdated(fn($state, callable $set) => $set('mileage_begin', $state)),
                        TextInput::make('mileage_end')
                            ->label(__('Mileage end'))
                            ->required()
                            ->suffix(' km')
                            ->numeric()
                            ->lazy()
                            ->minValue(fn($get) => ! empty($get('mileage_begin')) ? $get('mileage_begin') : 0),
                        TextInput::make('fuel_consumption_onboard_computer')
                            ->label(__('Fuel consumption onboard computer'))
                            ->suffix($powertrain['consumption_unit'])
                            ->numeric(),
                        TextInput::make('avg_speed')
                            ->label(__('Average speed'))
                            ->numeric()
                            ->suffix('km/h'),
                    ]),
                Fieldset::make('circumstances')
                    ->label(__('Circumstances'))
                    ->schema([
                        ToggleButtons::make('tires')
                            ->label(__('Tires'))
                            ->inline()
                            ->options(RefuelingTires::class)
                            ->default($previousRefueling->tires ?? null),
                        ToggleButtons::make('climate_control')
                            ->label(__('Climate control'))
                            ->multiple()
                            ->inline()
                            ->options(RefuelingClimateControl::class)
                            ->default($previousRefueling->climate_control ?? null),
                        ToggleButtons::make('routes')
                            ->label(__('Routes'))
                            ->inline()
                            ->multiple()
                            ->options(RefuelingRoutes::class)
                            ->default($previousRefueling->routes ?? null),
                        ToggleButtons::make('driving_style')
                            ->label(__('Driving style'))
                            ->inline()
                            ->options(RefuelingDrivingStyle::class)
                            ->default($previousRefueling->driving_style ?? null),
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
