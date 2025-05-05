<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxResource\Pages;
use App\Models\Tax;
use App\Models\Vehicle;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TaxResource extends Resource
{
    use IsMobile;

    protected static ?string $model = Tax::class;

    protected static ?string $navigationIcon = 'mdi-highway';

    public static function getNavigationLabel(): string
    {
        return __('Road taxes');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Road taxes');
    }

    public static function getModelLabel(): string
    {
        return __('Road tax');
    }

    public static function table(Table $table): Table
    {

        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Road taxes'))
                    ->modalContent(new HtmlString(__('Here you can your road taxes to get insight of their costs. This category includes only periodic costs paid to the government for providing access to the road network and associated facilities.')))
                    ->modalIcon('mdi-highway')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
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
                        ->formatStateUsing(function (Tax $tax) {
                            if (empty($tax->end_date)) {
                                $tax->end_date = __('Unknown');
                            }

                            return $tax->start_date->isoFormat('MMM D, Y') . ' t/m ' . $tax->end_date->isoFormat('MMM D, Y');
                        })
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('price')
                        ->sortable()
                        ->label(__('Price per month'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Total price average')),
                            Range::make()->label(__('Total price range')),
                        ]),
                    TextColumn::make('invoice_day')
                        ->sortable()
                        ->label(__('Invoice day'))
                        ->icon('gmdi-calendar-month-r')
                        ->suffix(__('th of the month')),
                ])
                    ->from('lg'),
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DatePicker::make('start_date')
                            ->label(__('Start date'))
                            ->native((new self)->isMobile()),
                        DatePicker::make('end_date')
                            ->label(__('End date'))
                            ->native((new self)->isMobile()),
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
            ->defaultSort('start_date', 'desc');
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
                DatePicker::make('start_date')
                    ->label(__('Start date'))
                    ->required()
                    ->native((new self)->isMobile())
                    ->displayFormat('d-m-Y')
                    ->maxDate(now()),
                DatePicker::make('end_date')
                    ->label(__('End date'))
                    ->native((new self)->isMobile())
                    ->displayFormat('d-m-Y'),
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
                    ->required()
                    ->minValue(1)
                    ->maxValue(31)
                    ->suffix(__('th of the month')),
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
