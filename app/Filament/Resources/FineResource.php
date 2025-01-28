<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FineResource\Pages;
use App\Models\Fine;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BuilderQuery;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

class FineResource extends Resource
{
    protected static ?string $model = Fine::class;

    protected static ?string $navigationIcon = 'maki-police';

    public static function getNavigationLabel(): string
    {
        return __('Fines');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Fines');
    }

    public static function getModelLabel(): string
    {
        return __('Fine');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                });
            })
            ->columns([
                Split::make([
                    TextColumn::make('icon')
                        ->formatStateUsing(
                            function (Fine $fine) {
                                if ($fine->icon) {
                                    return new HtmlString('<div class="min-h-16 flex items-center [&>svg]:max-h-8 [&>svg]:mx-auto">' .
                                        Blade::render("<x-icon :name='\$fine->icon' class='w-10 h-10 text-gray-500' />", ['fine' => $fine]) . '</div>');
                                }

                                return new HtmlString('<div class="min-h-16 flex items-center [&>svg]:max-h-8 [&>svg]:mx-auto">' .
                                    Blade::render("<x-icon name='maki-police' class='w-10 h-10 text-gray-500' />") . '</div>');
                            }
                        )->default(''),
                    TextColumn::make('fact')
                        ->label(__('Fact'))
                        ->description(fn(Fine $fine) => $fine->description)
                        ->sortable()
                        ->icon('gmdi-gavel-r')
                        ->summarize(Summarizer::make()
                            ->label(__('Most popular fact'))
                            ->using(function (BuilderQuery $query): string {
                                return $query->select('fact')
                                    ->selectRaw('COUNT(*) as count')
                                    ->groupBy('fact')
                                    ->orderByDesc('count')
                                    ->limit(1)
                                    ->pluck('fact')
                                    ->first();
                            })
                        ),
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
                            ->description(fn(Fine $fine) => '@ ' . $fine->road_distance_marker . ' km')
                            ->label(__('Road')),
                    ])
                        ->space(),
                    Stack::make([
                        TextColumn::make('location')
                            ->label(__('Location'))
                            ->icon('gmdi-location-on-r')
                            ->sortable(),
                        TextColumn::make('date')
                            ->sortable()
                            ->date()
                            ->icon('gmdi-calendar-month-r')
                            ->label(__('Date')),
                    ])
                        ->space(),
                    Stack::make([
                        TextColumn::make('price')
                            ->label(__('Price'))
                            ->icon('mdi-hand-coin-outline')
                            ->sortable()
                            ->money('EUR')
                            ->summarize([
                                Average::make()->label(__('Price average')),
                                Range::make()->label(__('Price range')),
                            ]),
                        TextColumn::make('payed')
                            ->label(__('Payed'))
                            ->icon(fn(Fine $fine) => $fine->payed ? 'gmdi-check-r' : 'gmdi-timer-s')
                            ->formatStateUsing(fn(Fine $fine) => $fine->payed ? __('Payed') : __('Pending payment'))
                            ->color(fn(Fine $fine) => $fine->payed ? 'success' : 'danger')
                            ->badge()
                            ->sortable(),
                        TextColumn::make('payment_method')
                            ->label(__('Payment method'))
                            ->icon(fn(string $state): string => match ($state) {
                                'cash' => 'mdi-hand-coin-outline',
                                'bank_card' => 'gmdi-credit-card',
                                'online' => 'gmdi-qr-code',
                                'direct_debit' => 'fas-file-invoice-dollar',
                                'bank_transfer' => 'mdi-bank-transfer',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'cash' => __('Cash'),
                                'bank_card' => __('Bank card'),
                                'online' => __('Online'),
                                'direct_debit' => __('Direct debit'),
                                'bank_transfer' => __('Bank transfer'),
                                default => '',
                            })
                            ->badge()
                            ->sortable(),
                    ])
                        ->space(),
                    Stack::make([
                        TextColumn::make('type')
                            ->label(__('Type'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => match ($state) {
                                'camera' => 'iconpark-surveillancecamerastwo',
                                'officer' => 'maki-police',
                                'traffic_stop' => 'mdi-car-emergency',
                                'automated' => 'gmdi-timer-s',
                                'traffic_control' => 'mdi-map-marker-radius',
                                'border_control' => 'fas-road-barrier',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'camera' => __('Camera'),
                                'officer' => __('Officer'),
                                'traffic_stop' => __('Traffic stop'),
                                'automated' => __('Automated'),
                                'traffic_control' => __('Traffic control'),
                                'border_control' => __('Border control'),
                            }),
                        TextColumn::make('provider')
                            ->label(__('Provider'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => match ($state) {
                                'police' => 'maki-police',
                                'local_police' => 'gmdi-local-police',
                                'road_operator' => 'mdi-highway',
                                'rdw' => 'gmdi-emoji-transportation-r',
                                'other_government' => 'gmdi-account-balance-r',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'police' => __('Police'),
                                'local_police' => __('Local police'),
                                'road_operator' => __('Road operator'),
                                'rdw' => __('National Road Transport Department'),
                                'other_government' => __('Other government agency'),
                                'other' => __('Other'),
                            }),
                        TextColumn::make('sanctions')
                            ->label(__('Sanctions'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => match ($state) {
                                'wok_status' => 'mdi-shield-alert',
                                'vehicle_seized' => 'mdi-car-door-lock',
                                'driving_ban' => 'mdi-car-clock',
                                'emg_course' => 'gmdi-school-r',
                                'drivers_license_confiscated' => 'mdi-credit-card-lock',
                                'arrested' => 'mdi-handcuffs',
                                default => '',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'wok_status' => __('WOK status'),
                                'vehicle_seized' => __('Vehicle seized'),
                                'driving_ban' => __('Driving ban'),
                                'emg_course' => __('EMG course'),
                                'drivers_license_confiscated' => __('Drivers license confiscated'),
                                'arrested' => __('Arrested'),
                            }),
                    ])
                        ->space(),
                ]),
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
            ])
            ->defaultSort('date', 'desc');
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
                        ToggleButtons::make('type')
                            ->label(__('Type'))
                            ->inline()
                            ->required()
                            ->options([
                                'camera' => __('Camera'),
                                'officer' => __('Officer'),
                                'traffic_stop' => __('Traffic stop'),
                                'automated' => __('Automated'),
                                'traffic_control' => __('Traffic control'),
                                'border_control' => __('Border control'),
                            ])
                            ->icons([
                                'camera' => 'iconpark-surveillancecamerastwo',
                                'officer' => 'maki-police',
                                'traffic_stop' => 'mdi-car-emergency',
                                'automated' => 'gmdi-timer-s',
                                'traffic_control' => 'mdi-map-marker-radius',
                                'border_control' => 'fas-road-barrier',
                            ]),
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        ToggleButtons::make('provider')
                            ->label(__('Provider'))
                            ->inline()
                            ->required()
                            ->options([
                                'police' => __('Police'),
                                'local_police' => __('Local police'),
                                'road_operator' => __('Road operator'),
                                'rdw' => __('National Road Transport Department'),
                                'other_government' => __('Other government agency'),
                                'other' => __('Other'),
                            ])
                            ->icons([
                                'police' => 'maki-police',
                                'local_police' => 'gmdi-local-police',
                                'road_operator' => 'mdi-highway',
                                'rdw' => 'gmdi-emoji-transportation-r',
                                'other_government' => 'gmdi-account-balance-r',
                            ]),
                    ]),
                Fieldset::make('Fact')
                    ->label(__('Fact'))
                    ->schema([
                        TextInput::make('fact')
                            ->label(__('Fact'))
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label(__('Description')),
                        IconPicker::make('icon')
                            ->label(__('Icon'))
                            ->sets([
                                'fontawesome-solid',
                                'google-material-design-icons',
                                'simple-icons',
                                'blade-mdi',
                            ])
                            ->columns(3),
                    ]),
                Fieldset::make('Location')
                    ->label(__('Location'))
                    ->schema([
                        Select::make('country')
                            ->label(__('Country'))
                            ->searchable()
                            ->native(false)
                            ->options($countriesOptions),
                        TextInput::make('location')
                            ->label(__('Location'))
                            ->maxLength(100),
                        TextInput::make('road')
                            ->label(__('Road'))
                            ->maxLength(100),
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
                        TextInput::make('road_distance_marker')
                            ->label(__('Road distance marker'))
                            ->numeric()
                            ->suffix('km')
                            ->step(0.01),
                    ]),
                Fieldset::make('Fine')
                    ->label(__('Fine'))
                    ->schema([
                        Toggle::make('fine')
                            ->label(__('Fine')),
                        Checkbox::make('payed')
                            ->label(__('Payed')),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01),
                        DatePicker::make('payment_date')
                            ->label(__('Payment date'))
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->options([
                                'cash' => __('Cash'),
                                'bank_card' => __('Bank card'),
                                'online' => __('Online'),
                                'direct_debit' => __('Direct debit'),
                                'bank_transfer' => __('Bank transfer'),
                            ])
                            ->icons([
                                'cash' => 'mdi-hand-coin-outline',
                                'bank_card' => 'gmdi-credit-card',
                                'online' => 'gmdi-qr-code',
                                'direct_debit' => 'fas-file-invoice-dollar',
                                'bank_transfer' => 'mdi-bank-transfer',
                            ]),
                    ]),
                Fieldset::make('Sanctions')
                    ->label(__('Sanctions'))
                    ->schema([
                        ToggleButtons::make('sanctions')
                            ->label(__('Sanctions'))
                            ->inline()
                            ->multiple()
                            ->options([
                                'wok_status' => __('WOK status'),
                                'vehicle_seized' => __('Vehicle seized'),
                                'driving_ban' => __('Driving ban'),
                                'emg_course' => __('EMG course'),
                                'drivers_license_confiscated' => __('Drivers license confiscated'),
                                'arrested' => __('Arrested'),
                            ])
                            ->icons([
                                'wok_status' => 'mdi-shield-alert',
                                'vehicle_seized' => 'mdi-car-door-lock',
                                'driving_ban' => 'mdi-car-clock',
                                'emg_course' => 'gmdi-school-r',
                                'drivers_license_confiscated' => 'mdi-credit-card-lock',
                                'arrested' => 'mdi-handcuffs',
                            ]),
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
            'index' => Pages\ListFines::route('/'),
            'create' => Pages\CreateFine::route('/create'),
            'edit' => Pages\EditFine::route('/{record}/edit'),
        ];
    }
}
