<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'gmdi-directions-car-filled-r';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('brand')
                    ->label(__('Brand'))
                    ->required()
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
                    ->inputMode('decimal'),
                TextInput::make('mileage_start')
                    ->label(__('Mileage begin'))
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
                Select::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->options(config('cars.fuel_types')),
                Toggle::make('private')
                    ->label(__('Private'))
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('cars.brands');
        $fuelTypes = config('cars.fuel_types');

        return $table
            ->columns([
                TextColumn::make('brand')
                    ->label(__('Brand'))
                    ->formatStateUsing(fn (string $state) => $brands[$state] ?? $state),
                TextColumn::make('model')
                    ->label(__('Model')),
                TextColumn::make('version')
                    ->label(__('Version')),
                TextColumn::make('engine')
                    ->label(__('Engine')),
                TextColumn::make('factory_specification_fuel_consumption')
                    ->label(__('Factory specification for fuel consumption')),
                TextColumn::make('mileage_start')
                    ->label(__('Mileage begin')),
                TextColumn::make('mileage_latest')
                    ->label(__('Latest mileage')),
                TextColumn::make('purchase_date')
                    ->label(__('Purchase date')),
                TextColumn::make('license_plate')
                    ->label(__('License plate')),
                TextColumn::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->formatStateUsing(fn (string $state) => $fuelTypes[$state] ?? $state),
                TextColumn::make('private')
                    ->label(__('Private')),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
