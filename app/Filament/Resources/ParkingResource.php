<?php

namespace App\Filament\Resources;

use App\Enums\ParkingPaymentMethod;
use App\Enums\ParkingType;
use App\Filament\Resources\ParkingResource\Pages;
use App\Models\Parking;
use App\Models\Vehicle;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ParkingResource extends Resource
{
    use IsMobile;

    protected static ?string $model = Parking::class;

    protected static ?string $navigationIcon = 'fas-parking';

    protected static ?string $slug = 'parking';

    public static function getNavigationLabel(): string
    {
        return __('Parking');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Parking');
    }

    public static function getModelLabel(): string
    {
        return __('Parking');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Parking'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of parking to get insight in their costs. This category includes all parking fees charged per session. These fees are paid to a company or government institution.')))
                    ->modalIcon('fas-parking')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Grid::make([
                    'xl' => 6,
                    'lg' => 4,
                    'md' => 2,
                ])
                ->schema([
                    TextColumn::make('start_time')
                        ->label(__('Date and time'))
                        ->sortable()
                        ->date()
                        ->formatStateUsing(function (Parking $parking) {
                            return $parking->start_time->isoFormat('MMM D, Y  H:mm') . ' - ' . $parking->end_time->isoFormat('MMM D, Y H:mm');
                        })
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('location')
                        ->label(__('Location'))
                        ->sortable()
                        ->icon('gmdi-location-on-r'),
                    TextColumn::make('company')
                        ->label(__('Company'))
                        ->sortable()
                        ->icon('mdi-office-building'),
                    TextColumn::make('price')
                        ->label(__('Price'))
                        ->icon('mdi-hand-coin-outline')
                        ->sortable()
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Price average')),
                            Range::make()->label(__('Price range')),
                        ]),
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->badge()
                        ->sortable()
                        ->color('gray')
                        ->icon(fn(string $state): string => ParkingType::from($state)->getIcon())
                        ->formatStateUsing(fn(string $state) => ParkingType::from($state)->getLabel()),
                    TextColumn::make('payment_method')
                        ->label(__('Payment method'))
                        ->badge()
                        ->sortable()
                        ->color('gray')
                        ->icon(fn(string $state): string => ParkingPaymentMethod::from($state)->getIcon())
                        ->formatStateUsing(fn(string $state) => ParkingPaymentMethod::from($state)->getLabel()),
                ])
            ])
            ->filters([
                Filter::make('time')
                    ->label(__('Time'))
                    ->form([
                        DateTimePicker::make('time_from')
                            ->label(__('Time from'))
                            ->native((new self)->isMobile()),
                        DateTimePicker::make('time_until')
                            ->label(__('Time until'))
                            ->native((new self)->isMobile()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['time_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['time_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_time', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['time_from'] && $data['time_until']) {
                            $indicators['time'] = __('Time from :from until :until', [
                                'from' => Carbon::parse($data['time_from'])->isoFormat('MMM D, Y H:mm'),
                                'until' => Carbon::parse($data['time_until'])->isoFormat('MMM D, Y H:mm'),
                            ]);
                        } else if ($data['time_from']) {
                            $indicators['time'] = __('Time from :from', [
                                'from' => Carbon::parse($data['time_from'])->isoFormat('MMM D, Y H:mm'),
                            ]);
                        } else if ($data['time_until']) {
                            $indicators['time'] = __('Date until :until', [
                                'until' => Carbon::parse($data['time_until'])->isoFormat('MMM D, Y H:mm'),
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
            ->defaultSort('start_time', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Basic')
                    ->label(__('Basic'))
                    ->schema([
                        ToggleButtons::make('type')
                            ->label(__('Type'))
                            ->inline()
                            ->options(ParkingType::class),
                        TextInput::make('location')
                            ->label(__('Location'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('company')
                            ->label(__('Company'))
                            ->maxLength(100),
                    ]),
                Fieldset::make('Period')
                    ->label(__('Period'))
                    ->schema([
                        DateTimePicker::make('start_time')
                            ->label(__('Start time'))
                            ->required()
                            ->native((new self)->isMobile())
                            ->default(now())
                            ->displayFormat('d-m-Y H:i'),
                        DateTimePicker::make('end_time')
                            ->label(__('End time'))
                            ->native((new self)->isMobile())
                            ->default(now())
                            ->displayFormat('d-m-Y H:i'),
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
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->options(ParkingPaymentMethod::class),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParking::route('/'),
            'create' => Pages\CreateParking::route('/create'),
            'edit' => Pages\EditParking::route('/{record}/edit'),
        ];
    }
}
