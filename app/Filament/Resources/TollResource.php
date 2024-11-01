<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TollResource\Pages;
use App\Models\Toll;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Livewire;

class TollResource extends Resource
{
    protected static ?string $model = Toll::class;

    protected static ?string $navigationIcon = 'maki-toll';

    public static function getNavigationLabel(): string
    {
        return __('Toll');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Toll');
    }

    public static function getModelLabel(): string
    {
        return __('Toll');
    }

    public static function form(Form $form): Form
    {
        $countries = config('countries');
        $countriesOptions = [];

        foreach ($countries as $key => $value) {
            $countriesOptions[$key] = $value['name'];
        }

        return $form
            ->schema([
                Select::make('vehicle_id')
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
                    ->grouped()
                    ->required()
                    ->options([
                        'location' => __('Location'),
                        'section' => __('Section'),
                    ])
                    ->icons([
                        'location' => 'gmdi-location-on-r',
                        'section' => 'gmdi-route-r',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set) => $set('type', $state)),
                ToggleButtons::make('payment_circumstances')
                    ->label(__('Payment circumstances'))
                    ->inline()
                    ->grouped()
                    ->options([
                        'toll_gate' => __('Toll gate'),
                        'camera' => __('Camera'),
                    ])
                    ->icons([
                        'toll_gate' => 'maki-toll',
                        'camera' => 'iconpark-surveillancecamerastwo',
                    ]),
                ToggleButtons::make('payment_method')
                    ->label(__('Payment method'))
                    ->inline()
                    ->grouped()
                    ->options([
                        'cash' => __('Cash'),
                        'card' => __('Card'),
                        'online' => __('Online'),
                        'toll_badge' => __('Toll badge'),
                        'app' => __('App'),
                    ])
                    ->icons([
                        'cash' => 'mdi-hand-coin-outline',
                        'card' => 'gmdi-credit-card',
                        'online' => 'gmdi-qr-code',
                        'toll_badge' => 'mdi-car-connected',
                        'app' => 'mdi-cellphone-wireless',
                    ]),
                TextInput::make('toll_company')
                    ->label(__('Toll company'))
                    ->maxLength(100),
                Select::make('country')
                    ->label(__('Country'))
                    ->searchable()
                    ->native(false)
                    ->options($countriesOptions),
                ToggleButtons::make('road_type')
                    ->label(__('Road type'))
                    ->inline()
                    ->grouped()
                    ->options([
                        'highway' => __('Highway'),
                        'secondary' => __('Secondary'),
                        'ring' => __('Ring'),
                        'provincial' => __('Provincial'),
                        'other' => __('Other'),
                    ])
                    ->icons([
                        'highway' => 'mdi-highway',
                        'secondary' => 'mdi-tunnel',
                        'ring' => 'mdi-reload',
                        'provincial' => 'fas-road',
                    ])
                    ->colors([
                        'highway' => 'danger',
                        'secondary' => 'info',
                        'ring' => 'success',
                        'provincial' => 'warning',
                    ]),
                TagsInput::make('road')
                    ->label(__('Road')),
                TextInput::make('start_location')
                    ->label(__('Start location'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('end_location')
                    ->label(__('End location'))
                    ->maxLength(100)
                    ->visible(fn($get) => $get('type') === 'section'),
                TextInput::make('price')
                    ->label(__('Price'))
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
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                })->orderByDesc('date');
            })
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('country')
                            ->sortable()
                            ->formatStateUsing(function ($record) {
                                return Livewire::mount('CountryFlag', [
                                    'country' => $record->country,
                                ]);
                            })
                            ->html()
                            ->label(__('Country')),
                        TextColumn::make('road')
                            ->sortable()
                            ->searchable()
                            ->formatStateUsing(function ($record) {
                                return Livewire::mount('RoadBadge', [
                                    'roadType' => $record->road_type,
                                    'road' => $record->road,
                                    'country' => $record->country,
                                ]);
                            })
                            ->html()
                            ->label(__('Road')),
                    ]),
                    TextColumn::make('date')
                        ->sortable()
                        ->date()
                        ->icon('gmdi-calendar-month-r')
                        ->label(__('Date')),
                    TextColumn::make('start_location')
                        ->sortable()
                        ->searchable()
                        ->icon(function (Toll $toll) {
                            if (! empty($toll->end_location)) {
                                return 'gmdi-route-r';
                            }

                            return 'gmdi-location-on-r';
                        })
                        ->label(__('Location/section'))
                        ->formatStateUsing(function (Toll $toll) {
                            if (! empty($toll->end_location)) {
                                return $toll->start_location . ' - ' . $toll->end_location;
                            }

                            return $toll->start_location;
                        }),
                    TextColumn::make('price')
                        ->label(__('Price'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Price average')),
                            Range::make()->label(__('Price range')),
                        ]),
                    TextColumn::make('toll_company')
                        ->sortable()
                        ->searchable()
                        ->icon('govicon-construction')
                        ->label(__('Toll company')),
                    Stack::make([
                        TextColumn::make('payment_circumstances')
                            ->label(__('Payment circumstances'))
                            ->color('gray')
                            ->badge()
                            ->icon(fn(string $state): string => match ($state) {
                                'toll_gate' => 'maki-toll',
                                'camera' => 'iconpark-surveillancecamerastwo',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'toll_gate' => __('Toll gate'),
                                'camera' => __('Camera'),
                            }),
                        TextColumn::make('payment_method')
                            ->label(__('Payment method'))
                            ->color('gray')
                            ->badge()
                            ->icon(fn(string $state): string => match ($state) {
                                'cash' => 'mdi-hand-coin-outline',
                                'card' => 'gmdi-credit-card',
                                'online' => 'gmdi-qr-code',
                                'toll_badge' => 'mdi-car-connected',
                                'app' => 'mdi-cellphone-wireless',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'cash' => __('Cash'),
                                'card' => __('Card'),
                                'online' => __('Online'),
                                'toll_badge' => __('Toll badge'),
                                'app' => __('App'),
                            }),
                    ])
                        ->space(),
                ]),
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
            'index' => Pages\ListTolls::route('/'),
            'create' => Pages\CreateToll::route('/create'),
            'edit' => Pages\EditToll::route('/{record}/edit'),
        ];
    }
}
