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
                    ->label(__('Mileage start'))
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
                Select::make('Fuel type')
                    ->label(__('Fuel type'))
                    ->options(config('cars.fuel_types')),
                Toggle::make('private')
                    ->label(__('Private'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
