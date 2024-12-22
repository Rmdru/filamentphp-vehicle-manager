<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsuranceResource\Pages;
use App\Filament\Resources\InsuranceResource\RelationManagers;
use App\Models\Insurance;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InsuranceResource extends Resource
{
    protected static ?string $model = Insurance::class;

    protected static ?string $navigationIcon = 'mdi-shield-car';

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

    public static function table(Table $table): Table
    {
        $insuranceTypes = config('insurances.types');

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                });
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('start_date')
                        ->sortable()
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
                        ->sortable()
                        ->badge()
                        ->default('')
                        ->formatStateUsing(fn(string $state): string => $insuranceTypes[$state]['name'] ?? __('Unknown'))
                        ->icon(fn(string $state): string => $insuranceTypes[$state]['icon'])
                        ->color('gray'),
                    TextColumn::make('insurance_company')
                        ->label(__('Insurance company'))
                        ->sortable()
                        ->icon('mdi-office-building'),
                    TextColumn::make('price')
                        ->label(__('Price per month'))
                        ->icon('mdi-hand-coin-outline')
                        ->sortable()
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Total price average')),
                            Range::make()->label(__('Total price range')),
                        ]),
                    TextColumn::make('invoice_day')
                        ->label(__('Invoice day'))
                        ->icon('gmdi-calendar-month-r')
                        ->sortable()
                        ->suffix(__('th of the month')),
                ]),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('start_date')
                            ->label(__('Start date'))
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label(__('End date'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] && $data['end_date']) {
                            $indicators['date'] = __('Date from :start until :end', [
                                'start' => Carbon::parse($data['start_date'])->isoFormat('MMM D, Y'),
                                'end' => Carbon::parse($data['end_date'])->isoFormat('MMM D, Y'),
                            ]);
                        } else if ($data['start_date']) {
                            $indicators['date'] = __('Date from :start', [
                                'start' => Carbon::parse($data['date_from'])->isoFormat('MMM D, Y'),
                            ]);
                        } else if ($data['end_date']) {
                            $indicators['date'] = __('Date until :end', [
                                'end' => Carbon::parse($data['end_date'])->isoFormat('MMM D, Y'),
                            ]);
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Basic')
                    ->label(__('Basic'))
                    ->schema([
                        Select::make('vehicle_id')
                            ->disabled()
                            ->label(__('Vehicle'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->relationship('vehicle')
                            ->default(fn(Vehicle $vehicle) => $vehicle->selected()->onlyDrivable()->first()->id ?? null)
                            ->options(function (Vehicle $vehicle) {
                                $vehicles = Vehicle::onlyDrivable()->get();

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
                            ->options(array_column(config('insurances.types'), 'name')),
                    ]),
                Fieldset::make('Payment')
                    ->label(__('Payment'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('Price per month'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                        TextInput::make('invoice_day')
                            ->label(__('Invoice day'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->required()
                            ->suffix(__('th of the month')),
                    ]),
                Fieldset::make('Period')
                    ->label(__('Period'))
                    ->schema([
                        DatePicker::make('start_date')
                            ->label(__('Start date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        DatePicker::make('end_date')
                            ->label(__('End date'))
                            ->native(false)
                            ->displayFormat('d-m-Y'),
                    ]),
            ]);
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
