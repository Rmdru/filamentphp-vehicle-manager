<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
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

class ProductResource extends Resource
{
    use IsMobile;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'mdi-oil';

    public static function getNavigationLabel(): string
    {
        return __('Products & accessory');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    public static function getModelLabel(): string
    {
        return __('Product');
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
                    ->options(function () {
                        $vehicles = Vehicle::all();

                        $vehicles->car = $vehicles->map(function ($index) {
                            return $index->full_name_with_license_plate;
                        });

                        return $vehicles->pluck('full_name_with_license_plate', 'id');
                    }),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
                DatePicker::make('date')
                    ->label(__('Date'))
                    ->maxDate(date('Y-m-d'))
                    ->native((new self)->isMobile())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Products & accessory'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of products such as engine fluids, practical accessory, interior products, cleaning products and safety equipment to get insight in their costs.')))
                    ->modalIcon('mdi-oil')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('vehicle', function ($query) {
                    $query->selected();
                });
            })
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->searchable()
                    ->sortable()
                    ->money('EUR')
                    ->icon('mdi-hand-coin-outline')
                    ->summarize([
                        Average::make()->label(__('Price average')),
                        Range::make()->label(__('Price range')),
                    ]),
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->icon('gmdi-calendar-month-r')
                    ->date()
                    ->searchable()
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
