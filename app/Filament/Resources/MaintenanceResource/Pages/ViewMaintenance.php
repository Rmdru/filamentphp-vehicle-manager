<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Models\Maintenance;
use Filament\Infolists;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\IconEntry\IconEntrySize;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenance extends ViewRecord
{
    protected static string $resource = MaintenanceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $brands = config('vehicles.brands');

        return $infolist
            ->schema([
                Infolists\Components\Fieldset::make('maintenance')
                    ->label(__('Maintenance'))
                    ->schema([
                        TextEntry::make('vehicle_id')
                            ->label(__('Vehicle'))
                            ->icon(fn(Maintenance $maintenance) => 'si-' . str($brands[$maintenance->vehicle->brand])->replace(' ', '')->lower())
                            ->formatStateUsing(fn(Maintenance $maintenance) => $brands[$maintenance->vehicle->brand] . " " . $maintenance->vehicle->model),
                        TextEntry::make('date')
                            ->label(__('Date'))
                            ->date()
                            ->icon('gmdi-calendar-month-r'),
                        TextEntry::make('garage')
                            ->label(__('Garage'))
                            ->placeholder(__('Unknown'))
                            ->icon('mdi-garage'),
                    ]),
                Infolists\Components\Fieldset::make('tasks')
                    ->label(__('Tasks'))
                    ->schema([
                        TextEntry::make('type_maintenance')
                            ->label(__('Type maintenance'))
                            ->badge()
                            ->placeholder('')
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'maintenance' => __('Maintenance'),
                                'small_maintenance' => __('Small maintenance'),
                                'big_maintenance' => __('Big maintenance'),
                                default => __('No maintenance'),
                            })
                            ->icon(fn(string $state): string => match ($state) {
                                'maintenance' => 'mdi-car-wrench',
                                'small_maintenance' => 'mdi-oil',
                                'big_maintenance' => 'mdi-engine',
                                default => 'gmdi-close-r',
                            })
                            ->color('gray'),
                        TextEntry::make('apk')
                            ->icon(fn(Maintenance $maintenance) => $maintenance->apk ? 'gmdi-security' : 'gmdi-close-r')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn(Maintenance $maintenance) => $maintenance->apk ? __('MOT') : __('No MOT'))
                            ->label(__('MOT')),
                        TextEntry::make('apk_date')
                            ->label(__('MOT date'))
                            ->date()
                            ->placeholder(__('Unknown'))
                            ->icon('gmdi-security'),
                        TextEntry::make('airco_check')
                            ->icon(fn(Maintenance $maintenance) => $maintenance->airco_check ? 'mdi-air-conditioner' : 'gmdi-close-r')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn(Maintenance $maintenance) => $maintenance->airco_check ? __('Airco check') : __('No airco check'))
                            ->label(__('Airco check')),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->placeholder(__('None'))
                            ->icon('gmdi-assignment-turned-in-o'),
                        TextEntry::make('total_price')
                            ->label(__('Total price'))
                            ->placeholder(__('Unknown'))
                            ->icon('mdi-hand-coin-outline')
                            ->money('EUR'),
                        RepeatableEntry::make('tasks')
                            ->schema([
                                IconEntry::make('icon')
                                    ->hiddenLabel()
                                    ->size(IconEntrySize::ExtraLarge)
                                    ->icon(fn(string $state): string => $state)
                                    ->hidden(fn(?string $state): bool => empty($state))
                                    ->default('mdi-car-wrench'),
                                TextEntry::make('task')
                                    ->hidden(fn(?string $state): bool => empty($state))
                                    ->hiddenLabel(),
                                TextEntry::make('price')
                                    ->hidden(fn(?string $state): bool => empty($state))
                                    ->money('EUR')
                                    ->default(0.00)
                                    ->hiddenLabel(),
                            ])
                            ->columns(3)
                            ->columnSpan(2)
                            ->grid(),
                    ]),
            ]);
    }
}
