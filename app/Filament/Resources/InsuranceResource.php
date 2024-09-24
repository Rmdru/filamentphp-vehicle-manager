<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsuranceResource\Pages;
use App\Filament\Resources\InsuranceResource\RelationManagers;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InsuranceResource extends Resource
{
    protected static ?string $model = Insurance::class;

    protected static ?string $navigationIcon = 'fas-hands-holding-circle';

    public static function getNavigationLabel(): string
    {
        return __('Insurances');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Insurances');
    }

    public static function getModelLabel(): string
    {
        return __('Insurance');
    }

    public static function form(Form $form): Form
    {
        $brands = config('vehicles.brands');

        return $form
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
                TextInput::make('insurance_company')
                    ->label(__('Insurance company'))
                    ->required()
                    ->maxLength(50),
                Select::make('type')
                    ->label(__('Type'))
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(config('insurances.types')),
                DatePicker::make('start_date')
                    ->label(__('Start date'))
                    ->required()
                    ->native(false)
                    ->displayFormat('d-m-Y')
                    ->maxDate(now()),
                DatePicker::make('end_date')
                    ->label(__('End date'))
                    ->native(false)
                    ->displayFormat('d-m-Y')
                    ->minDate(now()),
                TextInput::make('price')
                    ->label(__('Price per month'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('vehicles.brands');
        $insuranceTypes = config('insurances.types');

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                })->latest();
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('vehicle_id')
                        ->label(__('Vehicle'))
                        ->icon(fn (Insurance $insurance) => 'si-' . str($brands[$insurance->vehicle->brand])->replace(' ', '')->lower())
                        ->formatStateUsing(fn (Insurance $insurance) => $brands[$insurance->vehicle->brand] . " " . $insurance->vehicle->model),
                    TextColumn::make('start_date')
                        ->label(__('Start date'))
                        ->date()
                        ->formatStateUsing(function (Insurance $insurance) {
                            if (empty($insurance->end_date)) {
                                $insurance->end_date = __('Unknown');
                            }

                            return $insurance->start_date->isoFormat('MMM D, Y') . ' t/m ' . $insurance->end_date->isoFormat('MMM D, Y');
                        })
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->badge()
                        ->default('')
                        ->formatStateUsing(fn (string $state): string => $insuranceTypes[$state] ?? __('Unknown'))
                        ->icon(fn (string $state): string => match ($state) {
                            '0' => 'mdi-shield-outline',
                            '1' => 'mdi-shield-plus',
                            '2' => 'mdi-shield-star',
                            default => 'gmdi-warning-r',
                        })
                        ->color('gray'),
                    TextColumn::make('insurance_company')
                        ->label(__('Insurance company'))
                        ->icon('mdi-office-building'),
                    TextColumn::make('price')
                        ->label(__('Price per month'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR'),
                ])
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
            'index' => Pages\ListInsurances::route('/'),
            'create' => Pages\CreateInsurance::route('/create'),
            'edit' => Pages\EditInsurance::route('/{record}/edit'),
        ];
    }
}
