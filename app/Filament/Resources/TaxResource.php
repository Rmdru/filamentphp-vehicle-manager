<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxResource\Pages;
use App\Filament\Resources\TaxResource\RelationManagers;
use App\Models\Insurance;
use App\Models\Tax;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxResource extends Resource
{
    protected static ?string $model = Tax::class;

    protected static ?string $navigationIcon = 'fas-file-invoice-dollar';

    public static function getNavigationLabel(): string
    {
        return __('Taxes');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Taxes');
    }

    public static function getModelLabel(): string
    {
        return __('Tax');
    }

    public static function form(Form $form): Form
    {
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
                        ->icon(fn (Tax $tax) => 'si-' . str($brands[$tax->vehicle->brand])->replace(' ', '')->lower())
                        ->formatStateUsing(fn (Tax $tax) => $brands[$tax->vehicle->brand] . " " . $tax->vehicle->model),
                    TextColumn::make('start_date')
                        ->label(__('Start date'))
                        ->date()
                        ->formatStateUsing(function (Tax $tax) {
                            if (empty($tax->end_date)) {
                                $tax->end_date = __('Unknown');
                            }

                            return $tax->start_date->isoFormat('MMM D, Y') . ' t/m ' . $tax->end_date->isoFormat('MMM D, Y');
                        })
                        ->icon('gmdi-calendar-month-r'),
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
            'index' => Pages\ListTaxes::route('/'),
            'create' => Pages\CreateTax::route('/create'),
            'edit' => Pages\EditTax::route('/{record}/edit'),
        ];
    }
}
