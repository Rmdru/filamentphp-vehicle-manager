<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;

class ViewMaintenance extends ViewRecord
{
    protected static string $resource = MaintenanceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $brands = config('cars.brands');
        $fuelTypes = trans('powertrains');

        return $infolist
            ->schema([
                Infolists\Components\Fieldset::make('maintenance')
                    ->label(__('Maintenance'))
                    ->schema([
                        TextEntry::make('vehicle_id')
                            ->label(__('Vehicle'))
                            ->icon(fn (Maintenance $maintenance) => 'si-' . strtolower(str_replace(' ', '', $brands[$maintenance->vehicle->brand])))
                            ->formatStateUsing(fn (Maintenance $maintenance) => $brands[$maintenance->vehicle->brand] . " " . $maintenance->vehicle->model),
                        TextEntry::make('date')
                            ->label(__('Date'))
                            ->date()
                            ->icon('gmdi-calendar-month-r'),
                        TextEntry::make('garage')
                            ->label(__('Garage'))
                            ->icon('mdi-garage'),
                    ]),
                Infolists\Components\Fieldset::make('tasks')
                    ->label(__('Tasks'))
                    ->schema([
                        TextEntry::make('type_maintenance')
                            ->label(__('Type maintenance'))
                            ->badge()
                            ->default('')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'maintenance' => __('Maintenance'),
                                'small_maintenance' => __('Small maintenance'),
                                'Big maintenance' => __('Big maintenance'),
                                default => __('No maintenance'),
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'maintenance' => 'mdi-car-wrench',
                                'small_maintenance' => 'mdi-oil',
                                'big_maintenance' => 'mdi-engine',
                                default => 'gmdi-close-r',
                            })
                            ->color('gray'),
                        TextEntry::make('apk')
                            ->icon(fn (Maintenance $maintenance) => $maintenance->apk ? 'gmdi-security' : 'gmdi-close-r')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn (Maintenance $maintenance) => $maintenance->apk ? __('MOT') : __('No MOT'))
                            ->label(__('MOT')),
                        TextEntry::make('apk_date')
                            ->label(__('MOT date'))
                            ->date()
                            ->icon('gmdi-security'),
                        TextEntry::make('airco_check')
                            ->icon(fn (Maintenance $maintenance) => $maintenance->airco_check ? 'mdi-air-conditioner' : 'gmdi-close-r')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn (Maintenance $maintenance) => $maintenance->airco_check ? __('Airco check') : __('No airco check'))
                            ->label(__('Airco check')),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->default('-')
                            ->icon('gmdi-assignment-turned-in-o'),
                        TextEntry::make('total_price')
                            ->label(__('Total price'))
                            ->icon('mdi-hand-coin-outline')
                            ->money('EUR'),
                    ]),
            ]);
    }
}
