<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $brands = config('cars.brands');
        $fuelTypes = trans('powertrains');

        return $infolist
            ->schema([
                Infolists\Components\Fieldset::make('status')
                    ->label(__('Status'))
                    ->schema([
                        TextEntry::make('status')
                            ->icon('gmdi-check')
                            ->badge()
                            ->default('OK')
                            ->color('success')
                            ->columnSpan(2)
                            ->label(__('Status')),
                    ]),
                Infolists\Components\Fieldset::make('car_specifications')
                    ->label(__('Car specifications'))
                    ->schema([
                        TextEntry::make('brand')
                            ->label(__('Brand'))
                            ->icon(fn (Vehicle $vehicle) => 'si-' . strtolower(str_replace(' ', '', $brands[$vehicle->brand])))
                            ->formatStateUsing(fn (Vehicle $vehicle) => $brands[$vehicle->brand]),
                        TextEntry::make('model')
                            ->icon('gmdi-directions-car-filled-r')
                            ->label(__('Model')),
                        TextEntry::make('version')
                            ->icon('gmdi-star')
                            ->label(__('Version')),
                        TextEntry::make('engine')
                            ->label(__('Engine'))
                            ->placeholder('-')
                            ->icon('mdi-engine'),
                        TextEntry::make('factory_specification_fuel_consumption')
                            ->icon('gmdi-local-gas-station')
                            ->suffix(' l/100km')
                            ->placeholder('-')
                            ->label(__('Factory specification fuel consumption')),
                        TextEntry::make('powertrain')
                            ->icon('gmdi-local-gas-station')
                            ->placeholder('-')
                            ->label(__('Powertrain'))
                            ->formatStateUsing(fn (string $state) => $fuelTypes[$state] ?? $state),
                    ]),
                Infolists\Components\Fieldset::make('ownership')
                    ->label(__('Ownership'))
                    ->schema([
                        TextEntry::make('license_plate')
                            ->badge()
                            ->color('warning')
                            ->label(__('License plate')),
                        TextEntry::make('mileage_start')
                            ->icon('gmdi-route')
                            ->suffix(' km')
                            ->placeholder('-')
                            ->label(__('Mileage on purchase')),
                        TextEntry::make('mileage_start')
                            ->icon('gmdi-route')
                            ->suffix(' km')
                            ->placeholder('-')
                            ->label(__('Mileage'))
                            ->formatStateUsing(fn (Vehicle $vehicle) => $vehicle->mileage_latest ?? $vehicle->mileage_start),
                        TextEntry::make('purchase_date')
                            ->icon('gmdi-calendar-month-r')
                            ->date()
                            ->placeholder('-')
                            ->label(__('Purchase date')),
                    ]),
                Infolists\Components\Fieldset::make('Privacy')
                    ->label(__('Privacy'))
                    ->schema([
                        TextEntry::make('is_private')
                            ->icon(fn (Vehicle $vehicle) => $vehicle->is_private ? 'gmdi-lock' : 'gmdi-public')
                            ->badge()
                            ->default('OK')
                            ->color('gray')
                            ->formatStateUsing(fn (Vehicle $vehicle) => $vehicle->is_private ? __('Private') : __('Public'))
                            ->label(__('Privacy')),
                    ]),
            ]);
    }
}
