<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Livewire;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'gmdi-directions-car-filled-r';

    public static function getNavigationLabel(): string
    {
        return __('Manage my vehicles');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Vehicles');
    }

    public static function getModelLabel(): string
    {
        return __('Vehicle');
    }

    public static function form(Form $form): Form
    {
        $countries = config('countries');
        $countriesOptions = [];
        $powertrains = trans('powertrains');
        $powertrainsOptions = [];
        $fuelConsumptionUnits = [];
        $fuelTypes = trans('fuel_types');

        foreach ($countries as $key => $value) {
            $countriesOptions[$key] = $value['name'];
        }

        foreach ($powertrains as $key => $value) {
            $powertrainsOptions[$key] = $value['name'];
            $fuelConsumptionUnits[$key] = $value['fuel_consumption_unit'] ?? 'l/100km';
        }

        return $form
            ->schema([
                Fieldset::make('basic')
                    ->label(__('Basic'))
                    ->schema([
                        Select::make('brand')
                            ->label(__('Brand'))
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(config('vehicles.brands')),
                        TextInput::make('model')
                            ->label(__('Model'))
                            ->required()
                            ->maxLength(50),
                        TextInput::make('version')
                            ->label(__('Version'))
                            ->required()
                            ->maxLength(50),
                        TextInput::make('engine')
                            ->label(__('Engine'))
                            ->maxLength(50),
                        Select::make('powertrain')
                            ->label(__('Powertrain'))
                            ->native(false)
                            ->searchable()
                            ->options($powertrainsOptions)
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('powertrain', $state)),
                        TagsInput::make('fuel_types')
                            ->required()
                            ->label(__('Compatible fuel types'))
                            ->suggestions($fuelTypes),
                    ]),
                Fieldset::make('ownership')
                    ->label(__('Ownership'))
                    ->schema([
                        TextInput::make('mileage_start')
                            ->label(__('Mileage on purchase'))
                            ->suffix(' km')
                            ->numeric()
                            ->minValue(0),
                        DatePicker::make('purchase_date')
                            ->label(__('Purchase date'))
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        TextInput::make('purchase_price')
                            ->label(__('Purchase price'))
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01),
                        Select::make('country_registration')
                            ->label(__('Country of registration'))
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->options($countriesOptions)
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('license_plate_prefix', $state))
                            ->helperText(__('Is used for the license plate layout')),
                        TextInput::make('license_plate')
                            ->label(__('License plate'))
                            ->required()
                            ->prefix(fn(callable $get) => $countries[$get('license_plate_prefix')]['license_plate']['prefix'] ?? false),
                        ToggleButtons::make('status')
                            ->label(__('Status'))
                            ->inline()
                            ->required()
                            ->options([
                                'drivable' => __('Drivable'),
                                'suspended' => __('Suspended'),
                                'wok' => __('WOK status'),
                                'apk' => __('Invalid MOT'),
                                'seized' => __('Seized'),
                                'stolen' => __('Stolen'),
                                'sold' => __('Sold'),
                                'not_rollable' => __('Not rollable'),
                                'destroyed' => __('Destroyed'),
                            ])
                            ->icons([
                                'drivable' => 'mdi-speedometer',
                                'suspended' => 'mdi-garage',
                                'wok' => 'mdi-shield-off',
                                'apk' => 'mdi-shield-alert',
                                'seized' => 'maki-police',
                                'stolen' => 'mdi-lock-open-alert',
                                'sold' => 'gmdi-local-offer',
                                'not_rollable' => 'fas-car-crash',
                                'destroyed' => 'mdi-fire',
                            ]),
                    ]),
                Fieldset::make('privacy')
                    ->label(__('Privacy'))
                    ->schema([
                        Toggle::make('is_private')
                            ->label(__('Private')),
                    ]),
                Fieldset::make('specifications')
                    ->label(__('Specifications'))
                    ->schema([
                        Repeater::make('specifications')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name')),
                                TextInput::make('value')
                                    ->label(__('Value')),
                                TextInput::make('icon')
                                    ->label(__('Icon')),
                            ])
                            ->columnSpan(2)
                            ->columns(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('vehicles.brands');
        $powertrains = trans('powertrains');
        $powertrainsOptions = [];

        foreach ($powertrains as $key => $value) {
            $powertrainsOptions[$key] = $value['name'];
        }

        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Stack::make([
                        TextColumn::make('brand')
                            ->sortable()
                            ->searchable()
                            ->label(__('Vehicle'))
                            ->icon(fn(Vehicle $vehicle) => 'si-' . str($brands[$vehicle->brand])->replace(' ', '')->lower())
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->full_name),
                        TextColumn::make('mileage_start')
                            ->sortable()
                            ->searchable()
                            ->icon('gmdi-route')
                            ->suffix(' km')
                            ->label(__('Mileage'))
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->mileage_latest ?? $vehicle->mileage_start),
                        TextColumn::make('powertrain')
                            ->sortable()
                            ->icon('gmdi-local-gas-station')
                            ->placeholder(__('Unknown'))
                            ->sortable()
                            ->label(__('Powertrain'))
                            ->formatStateUsing(fn(string $state) => $powertrains[$state]['name'] ?? $state),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('license_plate')
                            ->sortable()
                            ->searchable()
                            ->formatStateUsing(function ($record) {
                                return Livewire::mount('license-plate', ['vehicleId' => $record->id]);
                            })
                            ->html()
                            ->label(__('License plate')),
                        TextColumn::make('status')
                            ->icon(fn(Vehicle $record): string => $record->getStatusBadge($record->id, 'icon'))
                            ->badge()
                            ->sortable()
                            ->default('OK')
                            ->formatStateUsing(fn(Vehicle $record): string => $record->getStatusBadge($record->id, 'text'))
                            ->color(fn(Vehicle $record): string => $record->getStatusBadge($record->id, 'color'))
                            ->label(__('Status')),
                        TextColumn::make('status')
                            ->icon(fn(string $state): string => match ($state) {
                                'drivable' => 'mdi-speedometer',
                                'suspended' => 'mdi-garage',
                                'wok' => 'mdi-shield-off',
                                'apk' => 'mdi-shield-alert',
                                'seized' => 'maki-police',
                                'stolen' => 'mdi-lock-open-alert',
                                'sold' => 'gmdi-local-offer',
                                'not_rollable' => 'fas-car-crash',
                                'destroyed' => 'mdi-fire',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'drivable' => __('Drivable'),
                                'suspended' => __('Suspended'),
                                'wok' => __('WOK status'),
                                'apk' => __('Invalid MOT'),
                                'seized' => __('Seized'),
                                'stolen' => __('Stolen'),
                                'sold' => __('Sold'),
                                'not_rollable' => __('Not rollable'),
                                'destroyed' => __('Destroyed'),
                            })
                            ->badge()
                            ->default('drivable')
                            ->color('gray')
                            ->sortable()
                            ->label(__('Status')),
                        TextColumn::make('is_private')
                            ->icon(fn(Vehicle $vehicle) => $vehicle->is_private ? 'gmdi-lock' : 'gmdi-public')
                            ->badge()
                            ->default('OK')
                            ->color('gray')
                            ->sortable()
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->is_private ? __('Private') : __('Public'))
                            ->label(__('Privacy')),
                    ])
                        ->space(),
                ]),
            ])
            ->defaultSort('purchase_date', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
