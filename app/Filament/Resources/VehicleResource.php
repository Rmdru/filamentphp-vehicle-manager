<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'gmdi-directions-car-filled-r';

    public static function getNavigationLabel(): string
    {
        return __('Vehicles');
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
        return $form
            ->schema([
                Fieldset::make('car_specifications')
                    ->label(__('Car specifications'))
                    ->schema([
                        Select::make('brand')
                            ->label(__('Brand'))
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(config('cars.brands')),
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
                        TextInput::make('factory_specification_fuel_consumption')
                            ->label(__('Factory specification for fuel consumption'))
                            ->numeric()
                            ->suffix(' l/100km')
                            ->inputMode('decimal'),
                        Select::make('powertrain')
                            ->label(__('Powertrain'))
                            ->native(false)
                            ->searchable()
                            ->options(trans('powertrains')),
                        ]),
                Fieldset::make('ownership')
                    ->label(__('Ownership'))
                    ->schema([
                        TextInput::make('mileage_start')
                            ->label(__('Mileage on purchase'))
                            ->suffix(' km')
                            ->numeric(),
                        DatePicker::make('purchase_date')
                            ->label(__('Purchase date'))
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        TextInput::make('license_plate')
                            ->label(__('License plate'))
                            ->required()
                            ->prefix('NL')
                            ->extraInputAttributes(['class' => '!text-black bg-yellow-600']),
                        ]),
                Fieldset::make('privacy')
                    ->label(__('Privacy'))
                    ->schema([
                        Toggle::make('is_private')
                            ->label(__('Private'))
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('cars.brands');
        $fuelTypes = trans('powertrains');

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::user()->id))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Stack::make([
                        TextColumn::make('brand')
                            ->sortable()
                            ->searchable()
                            ->label(__('Vehicle'))
                            ->icon(fn (Vehicle $vehicle) => 'si-' . strtolower(str_replace(' ', '', $brands[$vehicle->brand])))
                            ->formatStateUsing(fn (Vehicle $vehicle) => $brands[$vehicle->brand] . " " . $vehicle->model),
                        TextColumn::make('mileage_start')
                            ->sortable()
                            ->searchable()
                            ->icon('gmdi-route')
                            ->suffix(' km')
                            ->label(__('Mileage'))
                            ->formatStateUsing(fn (Vehicle $vehicle) => $vehicle->mileage_latest ?? $vehicle->mileage_start),
                        TextColumn::make('powertrain')
                            ->sortable()
                            ->icon('gmdi-local-gas-station')
                            ->placeholder('-')
                            ->label(__('Powertrain'))
                            ->formatStateUsing(fn (string $state) => $fuelTypes[$state] ?? $state),
                    ])
                        ->space(1),
                    Stack::make([
                        TextColumn::make('license_plate')
                            ->sortable()
                            ->badge()
                            ->color('warning')
                            ->searchable()
                            ->label(__('License plate')),
                        TextColumn::make('status')
                            ->icon('gmdi-check')
                            ->badge()
                            ->default('OK')
                            ->color('success')
                            ->label(__('Status')),
                        TextColumn::make('is_private')
                            ->icon(fn (Vehicle $vehicle) => $vehicle->is_private ? 'gmdi-lock' : 'gmdi-public')
                            ->badge()
                            ->default('OK')
                            ->color('gray')
                            ->formatStateUsing(fn (Vehicle $vehicle) => $vehicle->is_private ? __('Private') : __('Public'))
                            ->label(__('Privacy')),
                    ])
                        ->space(1),
                ])
            ])
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
