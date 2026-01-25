<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Enums\RefuelingClimateControl;
use App\Enums\RefuelingDrivingStyle;
use App\Enums\RefuelingPaymentMethod;
use App\Enums\RefuelingRoutes;
use App\Enums\RefuelingTires;
use App\Filament\Resources\RefuelingResource;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

class ViewRefueling extends ViewRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $gasStationLogos = config('refuelings.gas_station_logos');
        $vehicle = Filament::getTenant();
        $powertrain = trans('powertrains')[$vehicle->powertrain];

        return $infolist
            ->schema([
                Section::make('refueling')
                    ->columns()
                    ->icon('gmdi-local-gas-station-r')
                    ->heading(__('Refueling'))
                    ->schema([
                        Group::make([
                            Split::make([
                                TextEntry::make('gas_station')
                                    ->hiddenLabel()
                                    ->formatStateUsing(
                                        function (Refueling $refueling) use ($gasStationLogos) {
                                            $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

                                            $logo = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];

                                            return new HtmlString('<div class="w-5/12 min-h-16 max-h-40 flex items-center bg-white border border-gray-200 rounded p-2"><img class="max-h-40" src="' . $logo . '" /></div>');
                                        }
                                    ),
                                Group::make([
                                    TextEntry::make('gas_station')
                                        ->label(__('Gas station'))
                                        ->icon('gmdi-location-on-r'),
                                    TextEntry::make('service_by_attendant')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->color('success')
                                        ->icon('gmdi-star')
                                        ->formatStateUsing(fn(bool $state) => __('Service by attendant'))
                                        ->hidden(fn($state) => empty($state)),
                                ])
                            ])
                        ]),
                        Group::make([
                            ViewEntry::make('country')
                                ->label(__('Country'))
                                ->placeholder(__('Unknown'))
                                ->view('filament.tables.columns.country-flag', [
                                    'showName' => true,
                                ]),
                            TextEntry::make('date')
                                ->label(__('Date'))
                                ->date()
                                ->icon('gmdi-calendar-month-r'),
                        ]),
                    ]),
                Section::make('fuel')
                    ->columns()
                    ->icon('mdi-fuel')
                    ->heading(__('Fuel'))
                    ->schema([
                        TextEntry::make('fuel_type')
                            ->label(__('Fuel type'))
                            ->icon('gmdi-local-gas-station-r')
                            ->formatStateUsing(fn(string $state) => trans('fuel_types')[$state]),
                        TextEntry::make('amount')
                            ->label(__('Amount'))
                            ->icon(function (Refueling $refueling) {
                                if (str($refueling->fuel_type)->contains('electric', true)) {
                                    return 'mdi-battery-charging';
                                }

                                return 'mdi-fuel';
                            })
                            ->suffix($powertrain['unit_short']),
                        TextEntry::make('percentage')
                            ->label(__('Tank percentage after refueling'))
                            ->icon('mdi-water-percent')
                            ->suffix('%'),
                        TextEntry::make('unit_price')
                            ->label(__('Unit price'))
                            ->icon('gmdi-local-offer')
                            ->suffix('/' . $powertrain['unit_short'])
                            ->numeric(3)
                            ->prefix('€ '),
                        TextEntry::make('total_price')
                            ->label(__('Total price'))
                            ->icon('mdi-hand-coin-outline')
                            ->money('EUR'),
                        TextEntry::make('costs_per_kilometer')
                            ->label(__('Costs per kilometer'))
                            ->icon('uni-euro-circle-o')
                            ->suffix('/km')
                            ->money('EUR'),
                    ]),
                Section::make('car')
                    ->columns()
                    ->icon('gmdi-directions-car-r')
                    ->heading(__('Car'))
                    ->schema([
                        TextEntry::make('mileage_begin')
                            ->label(__('Mileage'))
                            ->icon('gmdi-route-r')
                            ->formatStateUsing(fn(Refueling $refueling) => $refueling->mileage_begin . ' km - ' . $refueling->mileage_end . ' km (' . $refueling->mileage_end - $refueling->mileage_begin . ' km)'),
                        TextEntry::make('fuel_consumption')
                            ->label(__('Fuel consumption'))
                            ->icon(function (Refueling $refueling) {
                                $fuelConsumption = $refueling->fuel_consumption;
                                $avgFuelConsumption = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('fuel_consumption');

                                if ($fuelConsumption > $avgFuelConsumption) {
                                    return 'gmdi-trending-up-r';
                                } else if ($fuelConsumption < $avgFuelConsumption) {
                                    return 'gmdi-trending-down-r';
                                }
                                
                                return 'mdi-approximately-equal';
                            })
                            ->badge()
                            ->color(function (Refueling $refueling) {
                                $fuelConsumption = $refueling->fuel_consumption;
                                $avgFuelConsumption = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('fuel_consumption');

                                if ($fuelConsumption > $avgFuelConsumption) {
                                    return 'danger';
                                } else if ($fuelConsumption < $avgFuelConsumption) {
                                    return 'success';
                                }
                                
                                return 'warning';
                            })
                            ->suffix($powertrain['consumption_unit']),
                        TextEntry::make('fuel_consumption_onboard_computer')
                            ->label(__('Fuel consumption onboard computer'))
                            ->icon('mdi-content-save')
                            ->suffix($powertrain['consumption_unit'])
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('avg_speed')
                            ->label(__('Average speed'))
                            ->hidden(fn($state) => empty($state))
                            ->icon(function (Refueling $refueling) {
                                $avgSpeed = $refueling->avg_speed;
                                $globalAvgSpeed = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('avg_speed');

                                if ($avgSpeed > $globalAvgSpeed) {
                                    return 'gmdi-trending-up-r';
                                } else if ($avgSpeed < $globalAvgSpeed) {
                                    return 'gmdi-trending-down-r';
                                }

                                return 'mdi-approximately-equal';
                            })
                            ->badge()
                            ->color(function (Refueling $refueling) {
                                $avgSpeed = $refueling->avg_speed;
                                $globalAvgSpeed = Refueling::where('vehicle_id', $refueling->vehicle_id)->avg('avg_speed');

                                if ($avgSpeed > $globalAvgSpeed) {
                                    return 'success';
                                } else if ($avgSpeed < $globalAvgSpeed) {
                                    return 'danger';
                                }
                                
                                return 'warning';
                            })
                            ->suffix('km/h'),
                    TextEntry::make('charge_time')
                        ->label(__('Charge time'))
                        ->hidden(fn($state) => empty($state))
                        ->formatStateUsing(fn($state) => $state ? $state->format('H:i') : null)
                        ->suffix(' h')
                        ->icon('mdi-timer-sand'),
                    ]),
                Section::make('cirumstances')
                    ->columns()
                    ->hidden(fn (Refueling $refueling) => empty($refueling->tires) && empty($refueling->climate_control) && empty($refueling->routes) && empty($refueling->driving_style))
                    ->icon('fas-cloud-sun-rain')
                    ->heading(__('Circumstances'))
                    ->schema([
                        TextEntry::make('tires')
                            ->badge()
                            ->label(__('Tires'))
                            ->color(fn(string $state): string => RefuelingTires::from($state)->getColor() ?? null)
                            ->icon(fn(string $state): string => RefuelingTires::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => RefuelingTires::from($state)->getLabel() ?? '')
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('climate_control')
                            ->badge()
                            ->label(__('Climate control'))
                            ->color(fn(string $state): string => RefuelingClimateControl::from($state)->getColor() ?? null)
                            ->icon(fn(string $state): string => RefuelingClimateControl::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => RefuelingClimateControl::from($state)->getLabel() ?? '')
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('routes')
                            ->badge()
                            ->label(__('Routes'))
                            ->color(fn(string $state): string => RefuelingRoutes::from($state)->getColor() ?? null)
                            ->icon(fn(string $state): string => RefuelingRoutes::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => RefuelingRoutes::from($state)->getLabel() ?? '')
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('driving_style')
                            ->badge()
                            ->label(__('Driving style'))
                            ->color(fn(string $state): string => RefuelingDrivingStyle::from($state)->getColor() ?? null)
                            ->icon(fn(string $state): string => RefuelingDrivingStyle::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => RefuelingDrivingStyle::from($state)->getLabel() ?? '')
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('comments')
                            ->icon('gmdi-text-fields-r'),
                    ]),
                Section::make('payment')
                    ->columns()
                    ->hidden(fn (Refueling $refueling) => empty($refueling->payment_method) && empty($refueling->discount))
                    ->icon('mdi-bank-transfer')
                    ->heading(__('Payment'))
                    ->schema([
                        TextEntry::make('payment_method')
                            ->badge()
                            ->label(__('Payment method'))
                            ->icon(fn(string $state): string => RefuelingPaymentMethod::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => RefuelingPaymentMethod::from($state)->getLabel() ?? '')
                            ->hidden(fn($state) => empty($state)),
                        TextEntry::make('discount')
                            ->label(__('Discount'))
                            ->hidden(fn($state) => empty($state)),
                    ])
            ]);
    }
}
