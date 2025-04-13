<?php

namespace App\Filament\Resources;

use App\Enums\MaintenancePaymentMethod;
use App\Enums\MaintenanceTypeMaintenance;
use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Traits\MaintenanceTypeOptions;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Symfony\Component\HtmlSanitizer\Visitor\Node\TextNode;

class MaintenanceResource extends Resource
{
    use MaintenanceTypeOptions;

    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'mdi-car-wrench';

    public static function getNavigationLabel(): string
    {
        return __('Maintenance & repairs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Maintenance');
    }

    public static function getModelLabel(): string
    {
        return __('Maintenance');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                });
            })
            ->headerActions([
                Action::make('small_checks')
                    ->label(__('Add small check'))
                    ->form([
                        Select::make('type_maintenance')
                            ->label(__('Type'))
                            ->options((new self)->getMaintenanceTypeOptions([
                                MaintenanceTypeMaintenance::TirePressureChecked,
                                MaintenanceTypeMaintenance::LiquidsChecked,
                            ]))
                            ->required(),
                        DatePicker::make('date')
                            ->default(now())
                            ->label(__('Date')),
                    ])
                    ->modalSubmitActionLabel(__('Save'))
                    ->action(function (array $data): void {
                        Maintenance::create([
                            'vehicle_id' => Session::get('vehicle_id'),
                            'type_maintenance' => $data['type_maintenance'],
                            'date' => $data['date'] ?? Carbon::today()->format('Y-m-d'),
                            'mileage' => Vehicle::selected()->first()->mileage_latest ?? Vehicle::selected()->first()->mileage_start,
                        ]);
                    }),
                Action::make('info')
                    ->modalHeading(__('Maintenance & repairs'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of maintenance, repairs and parts to get insight in their costs and get informed of important intervals. It is also possible to add small checks to get notified about their intervals. This category includes all costs incurred for preventive and periodic maintenance and repairs, as well as the replacement of worn and outdated parts. This does not include costs arising from damage or accidents.')))
                    ->modalIcon('mdi-car-wrench')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Split::make([
                    TextColumn::make('date')
                        ->label(__('Date'))
                        ->sortable()
                        ->date()
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('garage')
                        ->sortable()
                        ->label(__('Garage'))
                        ->icon('mdi-garage')
                        ->default(__('Unknown'))
                        ->searchable()
                        ->hidden(fn (Maintenance $maintenance) => in_array($maintenance->type_maintenance, [MaintenanceTypeMaintenance::TirePressureChecked->value, MaintenanceTypeMaintenance::LiquidsChecked->value])),
                    TextColumn::make('type_maintenance')
                        ->sortable()
                        ->label(__('Type maintenance'))
                        ->badge()
                        ->default('')
                        ->formatStateUsing(fn(string $state) => MaintenanceTypeMaintenance::from($state)->getLabel() ?? '')
                        ->color('gray'),
                    TextColumn::make('apk')
                        ->sortable()
                        ->badge()
                        ->color('gray')
                        ->formatStateUsing(fn(Maintenance $maintenance) => $maintenance->apk ? __('MOT') : __('No MOT'))
                        ->label(__('MOT'))
                        ->hidden(fn (Maintenance $maintenance) => in_array($maintenance->type_maintenance, [MaintenanceTypeMaintenance::TirePressureChecked->value, MaintenanceTypeMaintenance::LiquidsChecked->value])),
                    TextColumn::make('mileage')
                        ->sortable()
                        ->label(__('Mileage'))
                        ->icon('gmdi-route')
                        ->suffix(' km'),
                    TextColumn::make('total_price')
                        ->sortable()
                        ->label(__('Total price'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR')
                        ->default(__('Unknown'))
                        ->summarize([
                            Average::make()->label(__('Total price average')),
                            Range::make()->label(__('Total price range')),
                        ])
                        ->hidden(fn (Maintenance $maintenance) => in_array($maintenance->type_maintenance, [MaintenanceTypeMaintenance::TirePressureChecked->value, MaintenanceTypeMaintenance::LiquidsChecked->value])),
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
                ActionGroup::make([
                    EditAction::make(),
                    ReplicateAction::make()
                        ->label(__('Duplicate'))
                        ->icon('gmdi-file-copy-r')
                        ->requiresConfirmation()
                ]),
                ViewAction::make()
                    ->iconButton()
                    ->icon('gmdi-keyboard-arrow-right-r')
                    ->color('primary'),
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
                Fieldset::make('maintenance')
                    ->label(__('Maintenance'))
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
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d-m-Y')
                            ->maxDate(now()),
                        TextInput::make('garage')
                            ->label(__('Garage'))
                            ->maxLength(100),
                        TextInput::make('mileage')
                            ->label(__('Mileage'))
                            ->required()
                            ->suffix(' km')
                            ->numeric(),
                    ]),
                Fieldset::make('tasks')
                    ->label(__('Tasks'))
                    ->schema([
                        ToggleButtons::make('type_maintenance')
                            ->label(__('Type maintenance'))
                            ->inline()
                            ->options((new self)->getMaintenanceTypeOptions([
                                MaintenanceTypeMaintenance::Maintenance,
                                MaintenanceTypeMaintenance::SmallMaintenance,
                                MaintenanceTypeMaintenance::BigMaintenance,
                            ])),
                        Toggle::make('apk')
                            ->label(__('MOT')),
                        DatePicker::make('apk_date')
                            ->label(__('MOT date'))
                            ->native(false)
                            ->displayFormat('d-m-Y'),
                        Toggle::make('airco_check')
                            ->label(__('Airco check')),
                        Textarea::make('description')
                            ->label(__('Description')),
                        TextInput::make('total_price')
                            ->label(__('Total price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required()
                            ->prefix('€')
                            ->step(0.01),
                        ToggleButtons::make('payment_method')
                            ->label(__('Payment method'))
                            ->inline()
                            ->options(MaintenancePaymentMethod::class),
                        Repeater::make('tasks')
                            ->label(__('Tasks'))
                            ->schema([
                                TextInput::make('task')
                                    ->label(__('Task')),
                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->prefix('€')
                                    ->step(0.01),
                                IconPicker::make('icon')
                                    ->label(__('Icon'))
                                    ->sets([
                                        'fontawesome-solid',
                                        'google-material-design-icons',
                                        'simple-icons',
                                        'blade-mdi',
                                    ])
                                    ->columns(3),
                            ])
                    ])
                        ->columnSpan(2)
                        ->columns(),
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
