<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReconditioningResource\Pages;
use App\Models\Reconditioning;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Layout\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReconditioningResource extends Resource
{
    protected static ?string $model = Reconditioning::class;

    protected static ?string $navigationIcon = 'mdi-car-wash';

    public static function getNavigationLabel(): string
    {
        return __('Reconditioning');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reconditioning');
    }

    public static function getModelLabel(): string
    {
        return __('Reconditioning');
    }

    public static function form(Form $form): Form
    {
        return $form
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
                DatePicker::make('date')
                    ->label(__('Date'))
                    ->required()
                    ->native(false)
                    ->displayFormat('d-m-Y')
                    ->maxDate(now()),
                ToggleButtons::make('type')
                    ->label(__('Type'))
                    ->inline()
                    ->required()
                    ->multiple()
                    ->options([
                        'carwash' => __('Carwash'),
                        'exterior_cleaning' => __('Exterior cleaning'),
                        'interior_cleaning' => __('Interior cleaning'),
                        'engine_bay_cleaning' => __('Engine bay cleaning'),
                        'damage_repair' => __('Damage repair'),
                    ])
                    ->icons([
                        'carwash' => 'mdi-car-wash',
                        'interior_cleaning' => 'mdi-vacuum',
                        'exterior_cleaning' => 'gmdi-cleaning-services-r',
                        'engine_bay_cleaning' => 'mdi-engine',
                        'damage_repair' => 'mdi-spray',
                    ]),
                ToggleButtons::make('executor')
                    ->label(__('Executor'))
                    ->inline()
                    ->required()
                    ->options([
                        'myself' => __('Myself'),
                        'someone' => __('Someone else'),
                        'company' => __('Company'),
                    ]),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->prefix('€')
                    ->step(0.01),
                TextInput::make('location')
                    ->label(__('Location'))
                    ->maxLength(100),
                Textarea::make('description')
                    ->label(__('Description')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                })->orderByDesc('date');
            })
            ->columns([
                Split::make([
                    TextColumn::make('date')
                        ->sortable()
                        ->date()
                        ->icon('gmdi-calendar-month-r')
                        ->label(__('Date')),
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->icon(fn(string $state): string => match ($state) {
                            'carwash' => 'mdi-car-wash',
                            'interior_cleaning' => 'mdi-vacuum',
                            'exterior_cleaning' => 'gmdi-cleaning-services-r',
                            'engine_bay_cleaning' => 'mdi-engine',
                            'damage_repair' => 'mdi-spray',
                            default => '',
                        })
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'carwash' => __('Carwash'),
                            'exterior_cleaning' => __('Exterior cleaning'),
                            'interior_cleaning' => __('Interior cleaning'),
                            'engine_bay_cleaning' => __('Engine bay cleaning'),
                            'damage_repair' => __('Damage repair'),
                            default => '',
                        })
                        ->badge()
                        ->sortable(),
                    TextColumn::make('executor')
                        ->label(__('Executor'))
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'myself' => __('Myself'),
                            'someone' => __('Someone else'),
                            'company' => __('Company'),
                            default => '',
                        })
                        ->badge()
                        ->sortable(),
                    TextColumn::make('price')
                        ->label(__('Price'))
                        ->icon('mdi-hand-coin-outline')
                        ->sortable()
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Price average')),
                            Range::make()->label(__('Price range')),
                        ]),
                    TextColumn::make('location')
                        ->label(__('Location'))
                        ->icon('gmdi-location-on-r')
                        ->sortable(),
                ]),
                Panel::make([
                    TextColumn::make('description')
                        ->label(__('Description')),
                ])
                    ->collapsible(),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('Date from'))
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label(__('Date until'))
                            ->native(false),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReconditionings::route('/'),
            'create' => Pages\CreateReconditioning::route('/create'),
            'edit' => Pages\EditReconditioning::route('/{record}/edit'),
        ];
    }
}
