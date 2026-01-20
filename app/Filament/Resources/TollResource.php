<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoadType;
use App\Enums\TollPaymentCircumstances;
use App\Enums\TollPaymentMethod;
use App\Enums\TollType;
use App\Filament\Resources\TollResource\Pages;
use App\Models\Toll;
use App\Models\Vehicle;
use App\Traits\CountryOptions;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

class TollResource extends Resource
{
    use CountryOptions;
    use IsMobile;

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

    protected static ?string $slug = 'toll';

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Toll'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of toll sessions to get insight in their costs. This category includes all toll session fees. These fees are paid when the vehicle drives through a specific road section. These fees are paid to the road authority or toll company, usually a company or government agency.')))
                    ->modalIcon('maki-toll')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Split::make([
                    Stack::make([
                        ViewColumn::make('country')
                            ->view('filament.tables.columns.country-flag')
                            ->sortable()
                            ->hidden(fn ($state) => empty($state))
                            ->label(__('Country')),
                        ViewColumn::make('road')
                            ->view('filament.tables.columns.road-badge')
                            ->sortable()
                            ->searchable()
                            ->hidden(fn ($state) => empty($state))
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
                            ->color('primary')
                            ->badge()
                            ->icon(fn(string $state): string => TollPaymentCircumstances::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state) => TollPaymentCircumstances::from($state)->getLabel()),
                        TextColumn::make('payment_method')
                            ->label(__('Payment method'))
                            ->color('primary')
                            ->badge()
                            ->icon(fn(string $state): string => TollPaymentMethod::from($state)->getIcon())
                            ->formatStateUsing(fn(string $state) => TollPaymentMethod::from($state)->getLabel()),
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
                        ->modalIcon('gmdi-file-copy-r')
                        ->beforeReplicaSaved(function (Toll $replica): Toll {
                            $replica['date'] = today();

                            return $replica;
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native((new self)->isMobile())
                            ->displayFormat('d-m-Y')
                            ->default(now())
                            ->maxDate(now()),
                    ]),
                Fieldset::make('Location')
                    ->label(__('Location'))
                    ->schema([
                        ToggleButtons::make('type')
                            ->label(__('Type'))
                            ->inline()
                            ->required()
                            ->options(TollType::class)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('type', $state)),
                        TextInput::make('toll_company')
                            ->label(__('Toll company'))
                            ->maxLength(100),
                        Select::make('country')
                            ->label(__('Country'))
                            ->searchable()
                            ->native((new self)->isMobile())
                            ->options((new self())->getCountryOptions())
                            ->required(fn(callable $get) => $get('road') ?? false)
                            ->default(Filament::getTenant()->country_registration),
                        ToggleButtons::make('road_type')
                            ->label(__('Road type'))
                            ->inline()
                            ->options(RoadType::class)
                            ->required(fn(callable $get) => $get('road') ?? false),
                        TagsInput::make('road')
                            ->label(__('Road'))
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('road', $state)),
                        TextInput::make('start_location')
                            ->label(__('Start location'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('end_location')
                            ->label(__('End location'))
                            ->maxLength(100)
                            ->visible(fn($get) => $get('type') === 'section'),
                    ]),
                Fieldset::make('Payment')
                    ->label(__('Payment'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input, \'.\', \' \',)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                        ToggleButtons::make('payment_circumstances')
                            ->label(__('Payment circumstances'))
                            ->inline()
                            ->options(TollPaymentCircumstances::class),
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->options(TollPaymentMethod::class),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToll::route('/'),
            'create' => Pages\CreateToll::route('/create'),
            'edit' => Pages\EditToll::route('/{record}/edit'),
        ];
    }
}
