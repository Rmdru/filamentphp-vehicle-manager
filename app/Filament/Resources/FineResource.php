<?php

namespace App\Filament\Resources;

use App\Enums\FinePaymentMethod;
use App\Enums\FineProvider;
use App\Enums\FineSanction;
use App\Enums\FineType;
use App\Enums\RoadType;
use App\Filament\Resources\FineResource\Pages;
use App\Models\Fine;
use App\Models\Vehicle;
use App\Traits\CountryOptions;
use App\Traits\IsMobile;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ReplicateAction;
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
    use CountryOptions;
    use IsMobile;

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
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Fines'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of fines to get insight in their costs. This category only includes the costs resulting from violations committed. Other sanctions may also be recorded.')))
                    ->modalIcon('maki-police')
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
                            ->description(fn(Fine $fine) => ! empty($fine->road_distance_marker) ? '@ ' . $fine->road_distance_marker . ' km' : '')
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
                            ->icon(fn(string $state): string => FinePaymentMethod::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state): string => FinePaymentMethod::from($state)->getLabel())
                            ->badge()
                            ->sortable(),
                    ])
                        ->space(),
                    Stack::make([
                        TextColumn::make('type')
                            ->label(__('Type'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => FineType::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state) => FineType::from($state)->getLabel()),
                        TextColumn::make('provider')
                            ->label(__('Provider'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => FineProvider::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state) => FineProvider::from($state)->getLabel()),
                        TextColumn::make('sanctions')
                            ->label(__('Sanctions'))
                            ->sortable()
                            ->badge()
                            ->icon(fn(string $state): string => FineSanction::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state): string => FineSanction::from($state)->getLabel()),
                    ])
                        ->space(),
                ])
                    ->from('xl'),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('Date from'))
                            ->native((new self)->isMobile()),
                        DatePicker::make('date_until')
                            ->label(__('Date until'))
                            ->native((new self)->isMobile()),
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
                ActionGroup::make([
                    EditAction::make(),
                    ReplicateAction::make()
                        ->label(__('Duplicate'))
                        ->icon('gmdi-file-copy-r')
                        ->requiresConfirmation()
                ]),
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
                            ->native((new self)->isMobile())
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
                            ->inline()
                            ->required()
                            ->options(FineType::class),
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native((new self)->isMobile())
                            ->displayFormat('d-m-Y')
                            ->default(now())
                            ->maxDate(now()),
                        ToggleButtons::make('provider')
                            ->label(__('Provider'))
                            ->inline()
                            ->required()
                            ->options(FineProvider::class),
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
                            ->native((new self)->isMobile())
                            ->required(fn(callable $get) => $get('road') ?? false)
                            ->options((new self())->getCountryOptions())
                            ->default(Vehicle::selected()->first()->country_registration),
                        TextInput::make('location')
                            ->label(__('Location'))
                            ->maxLength(100),
                        TextInput::make('road')
                            ->label(__('Road'))
                            ->maxLength(100)
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('road', $state)),
                        ToggleButtons::make('road_type')
                            ->label(__('Road type'))
                            ->inline()
                            ->options(RoadType::class)
                            ->required(fn(callable $get) => $get('road') ?? false),
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
                            ->mask(RawJs::make('$money($input, \'.\', \' \',)'))
                            ->stripCharacters(',')
                            ->prefix('€')
                            ->step(0.01),
                        DatePicker::make('payment_date')
                            ->label(__('Payment date'))
                            ->native((new self)->isMobile())
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->options(FinePaymentMethod::class),
                    ]),
                Fieldset::make('Sanctions')
                    ->label(__('Sanctions'))
                    ->schema([
                        ToggleButtons::make('sanctions')
                            ->label(__('Sanctions'))
                            ->inline()
                            ->multiple()
                            ->options(FineSanction::class),
                    ]),
            ]);
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
