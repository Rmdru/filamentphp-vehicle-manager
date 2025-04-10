<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AccidentAttributeType;
use App\Enums\AccidentSituation;
use App\Enums\AccidentType;
use App\Filament\Resources\AccidentResource\Pages;
use App\Models\Accident;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AccidentResource extends Resource
{
    protected static ?string $model = Accident::class;

    protected static ?string $navigationIcon = 'fas-car-crash';

    public static function getNavigationLabel(): string
    {
        return __('Accidents & damage');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Accidents');
    }

    public static function getModelLabel(): string
    {
        return __('Accident');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('you')
                    ->label(__('You, your vehicle, your passengers and your objects'))
                    ->schema([
                        Select::make('vehicle_id')
                            ->disabled()
                            ->label(__('Vehicle'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->relationship('vehicle')
                            ->default(fn(Vehicle $vehicle) => $vehicle->selected()->first()->id ?? null)
                            ->options(function (Vehicle $vehicle) {
                                $vehicles = Vehicle::all();

                                $vehicles->car = $vehicles->map(function ($index) {
                                    return $index->full_name_with_license_plate;
                                });

                                return $vehicles->pluck('full_name_with_license_plate', 'id');
                            }),
                        ToggleButtons::make('type')
                            ->label(__('Type'))
                            ->required()
                            ->inline()
                            ->options(AccidentType::class)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('type', $state)),
                        DateTimePicker::make('datetime')
                            ->label(__('Date and time'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y H:i'),
                        TextInput::make('location')
                            ->label(__('Location'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('Description')),
                        Checkbox::make('guilty')
                            ->label(__('Guilty')),
                        ToggleButtons::make('situation')
                            ->label(__('Situation'))
                            ->inline()
                            ->multiple()
                            ->helperText(__('Select all that apply'))
                            ->options(AccidentSituation::class),
                        TextInput::make('damage_own')
                            ->label(__('Own damage'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText(__('This includes all damage: damage to the vehicle, damage to objects in the vehicle and costs of the injury of occupants.')),
                        TextInput::make('damage_own_insured')
                            ->label(__('Own damage insured'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText(__('This includes the amount that the insurance has reimbursed to you for your damage.')),
                        TextInput::make('damage_others')
                            ->label(__('Damage to others'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText(__('This includes all damage: damage to other vehicles or objects and costs of the injury of involved people.')),
                        TextInput::make('damage_others_insured')
                            ->label(__('Insured damage to others'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText(__('This includes the amount that the insurance has reimbursed to you for the damage of the others.')),
                ]),
                Fieldset::make('attributes')
                    ->label(__('Other involved attributes, vehicles, people, witness, animals and objects'))
                    ->schema([
                        Repeater::make('attributes')
                            ->hiddenLabel()
                            ->defaultItems(1)
                            ->schema([
                                TextInput::make('attribute_name')
                                    ->label(__('Name')),
                                ToggleButtons::make('attribute_type')
                                    ->label(__('Type'))
                                    ->inline()
                                    ->options(AccidentAttributeType::class),
                                Checkbox::make('attribute_guilty')
                                    ->label(__('Guilty')),
                                Checkbox::make('attribute_witness')
                                    ->label(__('Witness')),
                                ToggleButtons::make('attribute_situation')
                                    ->label(__('Situation'))
                                    ->inline()
                                    ->multiple()
                                    ->helperText(__('Select all that apply'))
                                    ->options(AccidentSituation::class),
                                Textarea::make('attribute_description')
                                    ->label(__('Description')),
                            ])
                            ->columnSpan(2)
                            ->columns(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Accidents & damage'))
                    ->modalContent(new HtmlString(__('Here you can add accidents and damages. You can add all costs of damage on you, your objects, your occupants and the vehicle as well as other involved vehicles, peoples, animals and objects.')))
                    ->modalIcon('fas-car-crash')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                });
            })
            ->columns([
                Split::make([
                    TextColumn::make('datetime')
                        ->label(__('Date and time'))
                        ->dateTime('d-m-Y H:i')
                        ->sortable()
                        ->searchable()
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('location')
                        ->label(__('Location'))
                        ->sortable()
                        ->searchable()
                        ->icon('gmdi-location-on-r'),
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->color('primary')
                        ->badge()
                        ->icon(fn(string $state): string => AccidentType::from($state)->getIcon())
                        ->formatStateUsing(fn(string $state): string => AccidentType::from($state)->getLabel())
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('situation')
                        ->label(__('Situation'))
                        ->color('primary')
                        ->badge()
                        ->icon(fn(string $state): string => AccidentSituation::from($state)->getIcon())
                        ->formatStateUsing(fn(string $state): string => AccidentSituation::from($state)->getLabel())
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('total_price')
                        ->label(__('Total price'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR')
                        ->sortable(),
                ])
                    ->from('xl'),
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
            'index' => Pages\ListAccidents::route('/'),
            'create' => Pages\CreateAccident::route('/create'),
            'edit' => Pages\EditAccident::route('/{record}/edit'),
        ];
    }
}
