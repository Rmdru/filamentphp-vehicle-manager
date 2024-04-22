<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefuelingResource\Pages;
use App\Filament\Resources\RefuelingResource\RelationManagers;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RefuelingResource extends Resource
{
    protected static ?string $model = Refueling::class;

    protected static ?string $navigationIcon = 'gmdi-local-gas-station';

    public static function form(Form $form): Form
    {
        $brands = config('cars.brands');

        return $form
            ->schema([
                Fieldset::make(__('Refueling'))
                    ->schema([
                        Select::make('vehicle')
                            ->label(__('Vehicle'))
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(function (Vehicle $vehicle) use ($brands) {
                                $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();

                                $vehicles->car = $vehicles->map(function ($index) use ($brands) {
                                    return $index->car = $brands[$index->brand] . ' ' . $index->model;
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
                            ->searchable()
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
                            ->suffix('km')
                            ->numeric(),
                        TextInput::make('mileage_end')
                            ->label(__('Mileage end'))
                            ->suffix('km')
                            ->numeric(),
                        TextInput::make('fuel_usage_onboard_computer')
                            ->label(__('Fuel usage onboard computer'))
                            ->suffix('l/100km')
                            ->numeric(),
                        ]),
                Fieldset::make(__('Circumstances'))
                    ->schema([

                    ]),
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
            'index' => Pages\ListRefuelings::route('/'),
            'create' => Pages\CreateRefueling::route('/create'),
            'edit' => Pages\EditRefueling::route('/{record}/edit'),
        ];
    }
}
