<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\MaintenanceTypeMaintenance;
use App\Enums\ParkingType;
use App\Enums\ReconditioningExecutor;
use App\Enums\ReconditioningType;
use App\Enums\ServiceType;
use App\Enums\TollType;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\Reconditioning;
use Carbon\CarbonImmutable
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Timeline extends Page
{
    protected static ?string $navigationIcon = 'gmdi-timeline-r';

    protected static string $view = 'filament.pages.timeline';

    public static function getNavigationLabel(): string
    {
        return __('Timeline');
    }

    public static function getModelLabel(): string
    {
        return __('Timeline');
    }

    public function getTitle(): string
    {
        return __('Timeline');
    }

    protected function getViewData(): array
    {
        $historyItems = $this->getHistoryItems();
        $predictions = $this->getPredictions();

        return [
            'historyItems' => $historyItems,
            'predictions' => $predictions,
        ];
    }

    private function getHistoryItems(): Collection
    {
        $insuranceType = config('insurances.types');
        $gasStationLogos = config('refuelings.gas_station_logos');
        $fuelTypes = trans('fuel_types');

        $vehicle = Vehicle::selected()
            ->with([
                'maintenances' => function ($query) {
                    $query->whereIn('type_maintenance', [
                        'small_maintenance',
                        'maintenance',
                        'big_maintenance',
                    ]);
                },
                'refuelings',
                'insurances',
                'taxes',
                'parking',
                'toll',
                'fines',
                'reconditionings',
                'vignettes',
                'environmentalStickers',
                'ferries',
                'products',
                'services',
            ])
            ->latest()
            ->first();

        $items = collect();

        foreach ($vehicle->maintenances as $maintenance) {
            $maintenance->icon = empty($maintenance->type_maintenance) && $maintenance->apk ? 'gmdi-security' : 'mdi-car-wrench';
            $maintenance->link = 'maintenances';
            $maintenance->heading = __('Maintenance');
            $badges = [];

            if (! empty($maintenance->type_maintenance)) {
                $badges[] = [
                    'title' => MaintenanceTypeMaintenance::from($maintenance->type_maintenance)->getLabel(),
                    'color' => 'primary',
                    'icon' => 'mdi-car-wrench',
                ];
            }

            if ($maintenance->apk) {
                $badges[] = [
                    'title' => __('MOT'),
                    'color' => 'primary',
                    'icon' => 'gmdi-security',
                ];
            }

            $maintenance->badges = $badges;


            $items->push($maintenance);
        }

        foreach ($vehicle->refuelings as $refueling) {
            $refueling->icon = 'gmdi-local-gas-station-r';
            $refueling->link = 'refuelings';
            $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];
            $refueling->logo = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];
            $refueling->heading = __('Refueling');
            $refueling->price = $refueling->total_price;
            $refueling->badges = [
                [
                    'title' => $fuelTypes[$refueling->fuel_type],
                    'color' => 'primary',
                    'icon' => '',
                ],
            ];
            $items->push($refueling);
        }

        foreach ($vehicle->insurances as $insurance) {
            foreach ($insurance->months as $month) {
                $insuranceClone = clone $insurance;

                $insuranceClone->icon = 'mdi-shield-car';
                $insuranceClone->link = 'insurances';
                $insuranceClone->date = Carbon::parse($month . '-' . $insuranceClone->invoice_day);
                $insuranceClone->heading = __('Insurance');
                $insuranceClone->badges = [
                    [
                        'title' => $insuranceType[$insurance->type]['name'],
                        'color' => 'primary',
                        'icon' => $insuranceType[$insurance->type]['icon'],
                    ],
                ];
                $items->push($insuranceClone);
            }
        }

        foreach ($vehicle->taxes as $tax) {
            foreach ($tax->months as $month) {
                $taxClone = clone $tax;
                $taxClone->icon = 'mdi-highway';
                $taxClone->link = 'taxes';
                $taxClone->date = Carbon::parse($month . '-' . $taxClone->invoice_day);
                $taxClone->heading = __('Road tax');
                $items->push($taxClone);
            }
        }

        foreach ($vehicle->parking as $parking) {
            $parking->icon = 'fas-parking';
            $parking->link = 'parking';
            $parking->date = $parking->end_time;
            $parking->heading = __('Parking');
            $parking->badges = [
                [
                    'title' => ParkingType::from($parking->type)->getLabel(),
                    'color' => 'primary',
                    'icon' => ParkingType::from($parking->type)->getIcon(),
                ],
            ];
            $items->push($parking);
        }

        foreach ($vehicle->toll as $toll) {
            $toll->icon = 'maki-toll';
            $toll->link = 'toll';
            $toll->heading = __('Toll');
            $toll->location = ! empty($toll->end_location) ? $toll->start_location . ' - ' . $toll->end_location : $toll->start_location;

            if (! empty($toll->road_type) && ! empty($toll->road) && ! empty($toll->country)) {
                $toll->countryFlag = $toll->country;
                $toll->roadConfig = [
                    'roadType' => $toll->road_type,
                    'road' => $toll->road,
                    'country' => $toll->country,
                ];
            }

            $items->push($toll);
        }

        foreach ($vehicle->fines as $fine) {
            $fine->icon = 'maki-police';
            $fine->link = 'fines';
            $fine->heading = __('Fine');
            $fine->badges = [
                [
                    'title' => $fine->payed ? __('Payed') : __('Pending payment'),
                    'color' => $fine->payed ? 'success' : 'danger',
                    'icon' => $fine->payed ? 'gmdi-check-r' : 'gmdi-timer-s',
                ],
            ];
            $items->push($fine);
        }

        foreach ($vehicle->reconditionings as $reconditioning) {
            $reconditioning->icon = 'mdi-car-wash';
            $reconditioning->heading = ReconditioningType::from($reconditioning->type)->getLabel();
            $reconditioning->badges = [
                [
                    'title' => ReconditioningExecutor::from($reconditioning->executor)->getLabel(),
                    'color' => 'primary',
                    'icon' => '',
                ],
            ];
            $reconditioning->link = 'reconditionings';
            $items->push($reconditioning);
        }

        foreach ($vehicle->vignettes as $vignette) {
            $vignette->icon = 'mdi-sticker-text';
            $vignette->heading = __('Vignette');
            $vignette->date = $vignette->start_date;
            $vignette->link = 'vignettes';
            $vignette->countryFlag = $vignette->country;
            $items->push($vignette);
        }

        foreach ($vehicle->environmentalStickers as $environmentalSticker) {
            $environmentalSticker->icon = 'fas-leaf';
            $environmentalSticker->heading = __('Environmental sticker');
            $environmentalSticker->date = $environmentalSticker->start_date;
            $environmentalSticker->link = 'environmental-stickers';
            $environmentalSticker->countryFlag = $environmentalSticker->country;
            $items->push($environmentalSticker);
        }

        foreach ($vehicle->ferries as $ferry) {
            $ferry->icon = 'mdi-ferry';
            $ferry->heading = __('Ferry');
            $ferry->date = $ferry->start_date;
            $ferry->link = 'ferries';
            $items->push($ferry);
        }

        foreach ($vehicle->products as $product) {
            $product->icon = 'mdi-oil';
            $product->heading = $product->name;
            $product->link = 'products';
            $items->push($product);
        }

        foreach ($vehicle->services as $service) {
            $service->icon = ServiceType::from($service->type)->getIcon();
            $service->heading = ServiceType::from($service->type)->getLabel();
            $service->link = 'services';
            $items->push($service);
        }

        return $items->sortByDesc('date')->groupBy(function ($item) {
            return Carbon::parse($item->date)->isoFormat('MMMM Y');
        });
    }

    private function getPredictions(): Collection
    {
        $vehicle = Vehicle::selected()
            ->addSelect([
                'apk' => Maintenance::select('id')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->where('apk', true)
                    ->orderByDesc('date')
                    ->limit(1),
                'maintenance' => Maintenance::select('id')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->whereIn('type_maintenance', [
                        MaintenanceTypeMaintenance::SmallMaintenance->value,
                        MaintenanceTypeMaintenance::Maintenance->value,
                        MaintenanceTypeMaintenance::BigMaintenance->value,
                    ])
                    ->orderByDesc('date')
                    ->limit(1),
                'reconditioning' => Reconditioning::select('id')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->where(function ($query) {
                        $query->where('type', 'LIKE', '%' . ReconditioningType::ExteriorCleaning->value . '%')
                            ->orWhere('type', 'LIKE', '%' . ReconditioningType::Carwash->value . '%');
                    })
                    ->orderByDesc('date')
                    ->limit(1),
            ])
            ->with(['insurances', 'taxes'])
            ->first();

        if (! $vehicle) {
            return collect();
        }

        $items = collect();

        $this->addNextMaintenancePrediction($vehicle, $items);

        $this->addInsurancePredictions($vehicle, $items);

        $this->addTaxPredictions($vehicle, $items);

        return $items->sortByDesc('date')->groupBy(function ($item) {
            return Carbon::parse($item->date)->isoFormat('MMMM Y');
        });
    }

    private function addNextMaintenancePrediction(Vehicle $vehicle, Collection &$items): void
    {
        $lastMaintenance = Maintenance::find($vehicle->maintenance);

        if ($lastMaintenance) {
            $nextMaintenanceDate = match ($lastMaintenance->type_maintenance) {
                MaintenanceTypeMaintenance::SmallMaintenance->value => Carbon::parse($lastMaintenance->date)->addMonths(6),
                MaintenanceTypeMaintenance::Maintenance->value => Carbon::parse($lastMaintenance->date)->addYear(),
                MaintenanceTypeMaintenance::BigMaintenance->value => Carbon::parse($lastMaintenance->date)->addYears(2),
                default => null,
            };

            if ($nextMaintenanceDate && $nextMaintenanceDate->isFuture()) {
                $maintenanceClone = clone $lastMaintenance;
                $maintenanceClone->icon = 'mdi-car-wrench';
                $maintenanceClone->heading = __('Maintenance');
                $maintenanceClone->date = $nextMaintenanceDate;
                $maintenanceClone->badges = [
                    [
                        'title' => MaintenanceTypeMaintenance::from($lastMaintenance->type_maintenance)->getLabel(),
                        'color' => 'primary',
                        'icon' => 'mdi-car-wrench',
                    ],
                ];
                $items->push($maintenanceClone);
            }
        }
    }

    private function addInsurancePredictions(Vehicle $vehicle, Collection &$items): void
    {
        $insuranceType = config('insurances.types');
        

        foreach ($vehicle->insurances as $insurance) {
            $nextInvoiceDate = $insurance->getNextInvoiceDate($insurance->start_date, $insurance->end_date, $insurance->invoice_day);
    
            if ($nextInvoiceDate) {
                $insuranceClone = clone $insurance;
                $insuranceClone->icon = 'mdi-shield-car';
                $insuranceClone->heading = __('Insurance');
                $insuranceClone->date = $nextInvoiceDate;
                $insuranceClone->badges = [
                    [
                        'title' => $insuranceType[$insurance->type]['name'],
                        'color' => 'primary',
                        'icon' => $insuranceType[$insurance->type]['icon'],
                    ],
                ];
                $items->push($insuranceClone);
            }
        }
    }

    private function addTaxPredictions(Vehicle $vehicle, Collection &$items): void
    {
        foreach ($vehicle->taxes as $tax) {
            $nextInvoiceDate = $tax->getNextInvoiceDate($tax->start_date, $tax->end_date, $tax->invoice_day);
    
            if ($nextInvoiceDate) {
                $taxClone = clone $tax;
                $taxClone->icon = 'mdi-highway';
                $taxClone->heading = __('Road tax');
                $taxClone->date = $nextInvoiceDate;
                $items->push($taxClone);
            }
        }
    }
}