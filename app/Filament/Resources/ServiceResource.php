<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ServiceType;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\Vehicle;
use App\Traits\IsMobile;
use Carbon\Carbon;
use Filament\Facades\Filament;
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

class ServiceResource extends Resource
{
    use IsMobile;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'mdi-tow-truck';


    public static function getNavigationLabel(): string
    {
        return __('Services');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Services');
    }

    public static function getModelLabel(): string
    {
        return __('Service');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ToggleButtons::make('type')
                    ->label(__('Type'))
                    ->inline()
                    ->options(ServiceType::class)
                    ->required(),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('price')
                    ->label(__('Price'))
                    ->numeric()
                    ->mask(RawJs::make('$money($input, \'.\', \' \',)'))
                    ->stripCharacters(',')
                    ->required()
                    ->prefix('€')
                    ->step(0.01),
                DatePicker::make('date')
                    ->label(__('Date'))
                    ->maxDate(date('Y-m-d'))
                    ->native((new self)->isMobile())
                    ->default(now())
                    ->required(),
                Textarea::make('comments')
                    ->label(__('Comments')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('info')
                    ->modalHeading(__('Services'))
                    ->modalContent(new HtmlString(__('Here you can add the cost of services you used en route. These services can be facilitated so that the driver can transport the passengers safely and comfortably.')))
                    ->modalIcon('mdi-tow-truck')
                    ->modalCancelActionLabel(__('Close'))
                    ->modalSubmitAction(false),
            ])
            ->columns([
                Split::make([
                    TextColumn::make('type')
                        ->label(__('Type'))
                        ->searchable()
                        ->sortable()
                        ->icon(fn(string $state): string => ServiceType::from($state)->getIcon())
                        ->formatStateUsing(fn(string $state) => ServiceType::from($state)->getLabel()),
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
                    ->from('lg'),
                Panel::make([
                    TextColumn::make('comments')
                        ->label(__('Comments'))
                        ->icon('gmdi-text-fields-r'),
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
                        ->beforeReplicaSaved(function (Service $replica): Service {
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
