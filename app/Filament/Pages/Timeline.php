<?php

namespace App\Filament\Pages;

use App\Models\Maintenance;
use App\Models\Reconditioning;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

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

    public function getTitle(): string|Htmlable
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
        $gasStationLogos = config('refuelings.gas_station_logos');
        $fuelTypes = trans('fuel_types');

        $vehicle = Vehicle::selected()
            ->with([
                'maintenances',
                'refuelings',
                'insurances',
                'taxes',
                'parkings',
                'tolls',
                'fines',
                'reconditionings',
            ])
            ->latest()
            ->first();

        foreach ($vehicle->maintenances as $maintenance) {
            $maintenance->icon = ! $maintenance->type_maintenance && $maintenance->apk ? 'gmdi-security' : 'mdi-car-wrench';
        }

        foreach ($vehicle->refuelings as $refueling) {
            $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

            $refueling->icon = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];
            $refueling->fuel_type = $fuelTypes[$refueling->fuel_type];
        }

        foreach ($vehicle->insurances as $insurance) {
            $insuranceType = config('insurances.types');

            foreach ($insurance->months as $month) {
                $insuranceClone = clone $insurance;
                $insuranceClone->date = Carbon::parse($month . '-' . $insuranceClone->invoice_day);
                $insuranceClone->icon = $insuranceType[$insurance->type]['icon'];
                $insuranceClone->typeIcon = $insuranceType[$insurance->type]['icon'];
                $insuranceClone->type = $insuranceType[$insurance->type]['name'];
                $vehicle->maintenances->push($insuranceClone);
            }
        }

        foreach ($vehicle->taxes as $tax) {
            foreach ($tax->months as $month) {
                $taxClone = clone $tax;
                $taxClone->date = Carbon::parse($month . '-' . $taxClone->invoice_day);
                $taxClone->icon = 'mdi-highway';
                $vehicle->maintenances->push($taxClone);
            }
        }

        foreach ($vehicle->parkings as $parking) {
            $typeIcon = match ($parking->type) {
                'street' => 'maki-parking-paid',
                'garage' => 'maki-parking-garage',
                default => '',
            };
            $parking->icon = $typeIcon;
            $parking->typeIcon = $typeIcon;
            $parking->date = $parking->end_time;
            $parking->type = match ($parking->type) {
                'street' => __('Street'),
                'garage' => __('Parking garage'),
            };

            $vehicle->maintenances->push($parking);
        }

        foreach ($vehicle->tolls as $toll) {
            $toll->typeIcon = match ($toll->type) {
                'location' => 'gmdi-location-on-r',
                'section' => 'gmdi-route-r',
                default => '',
            };
            $toll->type = match ($toll->type) {
                'location' => __('Location'),
                'section' => __('Section'),
            };

            if (! empty($toll->end_location)) {
                $toll->start_location = $toll->start_location . ' - ' . $toll->end_location;
            }

            $vehicle->maintenances->push($toll);
        }

        foreach ($vehicle->fines as $fine) {
            $fine->typeIcon = $fine->payed ? 'gmdi-check-r' : 'gmdi-timer-s';
            $fine->typeColor = $fine->payed ? 'success' : 'danger';
            $fine->type = $fine->payed ? __('Payed') : __('Pending payment');

            $vehicle->maintenances->push($fine);
        }

        foreach ($vehicle->reconditionings as $reconditionings) {

            $typeIcon = [];
            $type = [];

            foreach ($reconditionings->type as $reconditioningType) {
                $typeIcon[] = match ($reconditioningType) {
                    'carwash' => 'mdi-car-wash',
                    'interior_cleaning' => 'mdi-vacuum',
                    'exterior_cleaning' => 'gmdi-cleaning-services-r',
                    'engine_bay_cleaning' => 'mdi-engine',
                    'damage_repair' => 'mdi-spray',
                };

                $type[] = match ($reconditioningType) {
                    'carwash' => __('Carwash'),
                    'exterior_cleaning' => __('Exterior cleaning'),
                    'interior_cleaning' => __('Interior cleaning'),
                    'engine_bay_cleaning' => __('Engine bay cleaning'),
                    'damage_repair' => __('Damage repair'),
                };
            }

            $reconditionings->icon = 'mdi-car-wash';
            $reconditionings->typeIcon = $typeIcon;
            $reconditionings->type = $type;
            $reconditionings->executor = match($reconditionings->executor) {
                'myself' => __('Myself'),
                'someone' => __('Someone else'),
                'company' => __('Company'),
            };

            $vehicle->maintenances->push($reconditionings);
        }

        $items = $vehicle->maintenances->merge($vehicle->refuelings)
            ->sortByDesc(function ($item) {
                return $item->date;
            });

        $groupedItems = $items->groupBy(function ($item) {
            return $item->date->isoFormat('MMMM Y');
        });

        return $groupedItems;
    }

    public function getPredictions(): \Illuminate\Support\Collection
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
                    ->whereNotNull('type_maintenance')
                    ->orderByDesc('date')
                    ->limit(1),
                'reconditioning' => Reconditioning::select('id')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->where(function ($query) {
                        $query->where('type', 'LIKE', '%exterior_cleaning%')
                            ->orWhere('type', 'LIKE', '%carwash%');
                    })
                    ->orderByDesc('date')
                    ->limit(1),
            ])
            ->with([
                'insurances',
                'taxes',
            ])
            ->first();

        $items = collect();
        $apk = Maintenance::find($vehicle->apk);
        $maintenance = Maintenance::find($vehicle->maintenance);
        $wash = Reconditioning::find($vehicle->reconditioning);

        if (! empty($apk)) {
            $apk->title = __('MOT');
            $apk->categoryIcon = 'gmdi-security';
            $apk->date = $apk->date->addYear();

            $items->push($apk);
        }

        if (! empty($maintenance)) {
            $maintenance->title = __('Maintenance');
            $maintenance->categoryIcon = 'mdi-car-wrench';
            $maintenance->date = $maintenance->date->addYear();
            $maintenance->badges = collect();

            if ($maintenance->apk) {
                $maintenance->badges->push([
                    'color' => 'gray',
                    'title' => __('MOT'),
                    'icon' => 'gmdi-security',
                ]);
            }

            if ($maintenance->type_maintenance) {
                $maintenance->badges->push([
                    'color' => 'gray',
                    'title' => __('Maintenance'),
                    'icon' => 'mdi-car-wrench',
                ]);
            }

            $items->push($maintenance);
        }

        foreach ($vehicle->insurances as $insurance) {
            $nextInvoiceDate = $insurance->getNextInvoiceDate($insurance->start_date, $insurance->end_date, $insurance->invoice_day);
            $insuranceType = config('insurances.types');

            if ($nextInvoiceDate) {
                $insurance->date = $nextInvoiceDate;
                $insurance->title = __('Insurance');
                $insurance->categoryIcon = 'mdi-shield-car';
                $insurance->icon = $insuranceType[$insurance->type]['icon'];
                $insurance->badges = collect();

                if ($insurance->type) {
                    $insurance->badges->push([
                        'color' => 'gray',
                        'title' => $insuranceType[$insurance->type]['name'],
                        'icon' => $insuranceType[$insurance->type]['icon'],
                    ]);
                }

                $items->push($insurance);
            }
        }

        foreach ($vehicle->taxes as $tax) {
            $nextInvoiceDate = $tax->getNextInvoiceDate($tax->start_date, $tax->end_date, $tax->invoice_day);

            if ($nextInvoiceDate) {
                $tax->date = $nextInvoiceDate;
                $tax->title = __('Road tax');
                $tax->categoryIcon = 'mdi-highway';
                $items->push($tax);
            }
        }

        if (! empty($wash)) {
            $wash->title = __('Washing');
            $wash->categoryIcon = 'mdi-car-wash';
            $wash->date = $wash->date->addMonth();

            $items->push($wash);
        }

        $groupedItems = $items->sortByDesc(function ($item) {
            return $item->date;
        })->groupBy(function ($item) {
            return Carbon::parse($item->date)->isoFormat('MMMM Y');
        });

        return $groupedItems ?? collect();
    }
}
