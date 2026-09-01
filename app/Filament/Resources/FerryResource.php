<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FerryResource\Pages;
use App\Models\Ferry;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
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

class FerryResource extends Resource
{
    use IsMobile;
    
    protected static ?string $model = Ferry::class;

    protected static ?string $navigationIcon = 'mdi-train-car-flatbed-car';

    public static function getNavigationLabel(): string
    {
        return __('Transport trains & ferry services');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Transport trains & ferry services');
    }

    public static function getModelLabel(): string
    {
        return __('Transport train & ferry service');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('start_location')
                    ->label(__('Start location'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('end_location')
                    ->label(__('End location'))
                    ->required()
                    ->maxLength(100),
                DateTimePicker::make('start_date')
                    ->label(__('Start date'))
                    ->required()
                    ->native((new self)->isMobile())
                    ->displayFormat('d-m-Y H:i')
                    ->maxDate(now()),
                DateTimePicker::make('end_date')
                    ->label(__('End date'))
                    ->required()
                    ->native((new self)->isMobile())
                    ->displayFormat('d-m-Y H:i'),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input, \'.\', \'\',)'))
                    ->stripCharacters(',')
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Ferries'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of addional transport methods such as ferries and transportation trains to get insight in their costs.')))
                    ->modalIcon('mdi-ferry')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Grid::make([
                    'xl' => 3,
                    'lg' => 2,
                    'md' => 1,
                ])
                ->schema([
                    TextColumn::make('start_location')
                        ->sortable()
                        ->searchable()
                        ->icon('gmdi-route-r')
                        ->label(__('Section'))
                        ->formatStateUsing(function (Ferry $ferry) {
                            return $ferry->start_location . ' - ' . $ferry->end_location;
                        }),
                    TextColumn::make('start_date')
                        ->label(__('Date'))
                        ->sortable()
                        ->dateTime()
                        ->formatStateUsing(function (Ferry $ferry) {
                            return $ferry->start_date->isoFormat('MMM D, Y H:mm') . ' - ' . $ferry->end_date->isoFormat('MMM D, Y H:mm');
                        })
                        ->icon('gmdi-calendar-month-r'),
                    TextColumn::make('price')
                        ->label(__('Price'))
                        ->icon('mdi-hand-coin-outline')
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Price average')),
                            Range::make()->label(__('Price range')),
                        ]),
                ])
            ])
            ->filters([
                Filter::make('date')
                    ->label(__('Date'))
                    ->form([
                        DateTimePicker::make('start_date')
                            ->label(__('Start date'))
                            ->native((new self)->isMobile()),
                        DateTimePicker::make('end_date')
                            ->label(__('End date'))
                            ->native((new self)->isMobile()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->where('end_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] && $data['end_date']) {
                            $indicators['date'] = __('Date from :start until :end', [
                                'start' => Carbon::parse($data['start_date'])->isoFormat('MMM D, Y H:mm'),
                                'end' => Carbon::parse($data['end_date'])->isoFormat('MMM D, Y H:mm'),
                            ]);
                        } else if ($data['start_date']) {
                            $indicators['date'] = __('Date from :start', [
                                'start' => Carbon::parse($data['start_date'])->isoFormat('MMM D, Y H:mm'),
                            ]);
                        } else if ($data['end_date']) {
                            $indicators['date'] = __('Date until :end', [
                                'end' => Carbon::parse($data['end_date'])->isoFormat('MMM D, Y H:mm'),
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
                        ->beforeReplicaSaved(function (Ferry $replica): Ferry {
                            $replica['start_date'] = now();

                            return $replica;
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFerries::route('/'),
            'create' => Pages\CreateFerry::route('/create'),
            'edit' => Pages\EditFerry::route('/{record}/edit'),
        ];
    }
}
