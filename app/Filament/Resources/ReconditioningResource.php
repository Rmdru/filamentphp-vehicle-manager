<?php

namespace App\Filament\Resources;

use App\Enums\ReconditioningExecutor;
use App\Enums\ReconditioningType;
use App\Filament\Resources\ReconditioningResource\Pages;
use App\Models\Reconditioning;
use App\Models\Vehicle;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ReconditioningResource extends Resource
{
    use IsMobile;

    protected static ?string $model = Reconditioning::class;

    protected static ?string $navigationIcon = 'mdi-car-wash';

    public static function getNavigationLabel(): string
    {
        return __('Reconditioning & washing');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reconditioning');
    }

    public static function getModelLabel(): string
    {
        return __('Reconditioning');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Reconditioning & washing'))
                    ->modalContent(new HtmlString(__('Here you can add the costs of reconditioning and washing to get insight in thir costs. This category only includes costs incurred to clean, detail or restore the vehicle to its original optical condition.')))
                    ->modalIcon('mdi-car-wash')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Split::make([
                    TextColumn::make('date')
                        ->sortable()
                        ->date()
                        ->icon('gmdi-calendar-month-r')
                        ->label(__('Date')),
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->formatStateUsing(fn(string $state): string => ReconditioningType::from($state)->getLabel() ?? '')
                        ->badge()
                        ->sortable(),
                    TextColumn::make('executor')
                        ->label(__('Executor'))
                        ->formatStateUsing(fn(string $state): string => ReconditioningExecutor::from($state)->getLabel() ?? '')
                        ->badge()
                        ->sortable(),
                    TextColumn::make('price')
                        ->label(__('Price'))
                        ->icon('mdi-hand-coin-outline')
                        ->sortable()
                        ->money('EUR')
                        ->summarize([
                            Average::make()->label(__('Price average')),
                            Range::make()->label(__('Price range')),
                        ]),
                    TextColumn::make('location')
                        ->label(__('Location'))
                        ->icon('gmdi-location-on-r')
                        ->sortable(),
                ])
                    ->from('lg'),
                Panel::make([
                    TextColumn::make('description')
                        ->label(__('Description')),
                ])
                    ->collapsible(),
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
                        ->beforeReplicaSaved(function (Reconditioning $replica): Reconditioning {
                            $replica['date'] = today();

                            return $replica;
                        })
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
                DatePicker::make('date')
                    ->label(__('Date'))
                    ->required()
                    ->native((new self)->isMobile())
                    ->displayFormat('d-m-Y')
                    ->default(now())
                    ->maxDate(now()),
                ToggleButtons::make('type')
                    ->label(__('Type'))
                    ->inline()
                    ->required()
                    ->options(ReconditioningType::class),
                ToggleButtons::make('executor')
                    ->label(__('Executor'))
                    ->inline()
                    ->required()
                    ->options(ReconditioningExecutor::class),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input, \'.\', \' \',)'))
                    ->stripCharacters(',')
                    ->prefix('€')
                    ->step(0.01),
                TextInput::make('location')
                    ->label(__('Location'))
                    ->maxLength(100),
                Textarea::make('description')
                    ->label(__('Description')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReconditionings::route('/'),
            'create' => Pages\CreateReconditioning::route('/create'),
            'edit' => Pages\EditReconditioning::route('/{record}/edit'),
        ];
    }
}
