<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\Input;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'mdi-car-wrench';

    public static function getNavigationLabel(): string
    {
        return __('Maintenances');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Maintenances');
    }

    public static function getModelLabel(): string
    {
        return __('Maintenance');
    }

    public static function form(Form $form): Form
    {
        $brands = config('cars.brands');

        return $form
            ->schema([
                Fieldset::make('maintenance')
                    ->label(__('Maintenance'))
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
                        TextInput::make('garage')
                            ->label(__('Garage'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('mileage_begin')
                            ->label(__('Mileage'))
                            ->required()
                            ->suffix(' km')
                            ->numeric(),
                    ]),
                Fieldset::make('tasks')
                    ->label(__('Tasks'))
                    ->schema([
                        Forms\Components\ToggleButtons::make('type_maintenance')
                            ->label(__('Type maintenance'))
                            ->inline()
                            ->grouped()
                            ->options([
                                'maintenance' => __('Maintenance'),
                                'small_maintenance' => __('Small maintenance'),
                                'big_maintenance' => __('Big maintenance'),
                            ]),
                        Toggle::make('apk')
                            ->label(__('MOT')),
                        Toggle::make('airco_check')
                            ->label(__('Airco check')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Description')),
                        TextInput::make('total_price')
                            ->label(__('Total price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('cars.brands');

        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('vehicle_id')
                        ->label(__('Vehicle'))
                        ->icon(fn (Maintenance $maintenance) => 'si-' . strtolower(str_replace(' ', '', $brands[$maintenance->vehicle->brand])))
                        ->formatStateUsing(fn (Maintenance $maintenance) => $brands[$maintenance->vehicle->brand] . " " . $maintenance->vehicle->model),
                    TextColumn::make('date')
                        ->label(__('Date'))
                        ->date()
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('type_maintenance')
                        ->label(__('Type maintenance'))
                        ->badge()
                        ->default('')
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'maintenance' => __('Maintenance'),
                            'small_maintenance' => __('Small maintenance'),
                            'Big maintenance' => __('Big maintenance'),
                            default => __('No maintenance'),
                        })
                        ->icon(fn (string $state): string => match ($state) {
                            'maintenance' => 'mdi-car-wrench',
                            'small_maintenance' => 'mdi-oil',
                            'big_maintenance' => 'mdi-engine',
                            default => 'gmdi-close-r',
                        })
                        ->color('gray'),
                    TextColumn::make('apk')
                        ->icon(fn (Maintenance $maintenance) => $maintenance->apk ? 'gmdi-security' : 'gmdi-close-r')
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn (Maintenance $maintenance) => $maintenance->apk ? __('MOT') : __('No MOT'))
                        ->label(__('MOT')),
                    TextColumn::make('mileage_begin')
                        ->label(__('Mileage'))
                        ->icon('gmdi-route'),
                    TextColumn::make('total_price')
                        ->label(__('Total price'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR'),
                ]),
            ])
            ->filters([
                //
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'view' => Pages\ViewMaintenance::route('/{record}'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
