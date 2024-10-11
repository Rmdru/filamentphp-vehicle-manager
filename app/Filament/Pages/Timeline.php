<?php

namespace App\Filament\Pages;

use App\Models\Maintenance;
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
            foreach ($insurance->months as $month) {
                $insuranceClone = clone $insurance;
                $insuranceClone->date = Carbon::parse($month . '-' . $insuranceClone->invoice_day);
                $insuranceClone->icon = 'mdi-shield-car';
                $insuranceClone->typeIcon = match ($insurance->type) {
                    '0' => 'mdi-shield-outline',
                    '1' => 'mdi-shield-plus',
                    '2' => 'mdi-shield-star',
                    default => 'mdi-shield-car',
                };
                $insuranceClone->type = config('insurances.types')[$insurance->type];
                $vehicle->maintenances->push($insuranceClone);
            }
        }

        foreach ($vehicle->taxes as $tax) {
            foreach ($tax->months as $month) {
                $taxClone = clone $tax;
                $taxClone->date = Carbon::parse($month . '-' . $taxClone->invoice_day);
                $taxClone->icon = 'fas-file-invoice-dollar';
                $vehicle->maintenances->push($taxClone);
            }
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
                    ->where('apk', 1)
                    ->latest()
                    ->limit(1),
                'maintenance' => Maintenance::select('id')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->whereNotNull('type_maintenance')
                    ->latest()
                    ->limit(1),
            ])
            ->with([
                'insurances',
                'taxes',
            ])
            ->latest()
            ->first();

        $items = collect();
        $apk = Maintenance::find($vehicle->apk);
        $maintenance = Maintenance::find($vehicle->maintenance);

        if (! empty($apk)) {
            $apk->title = __('MOT');
            $apk->categoryIcon = 'gmdi-security';
            $apk->date = $apk->date->addYear();
            $items->push($maintenance);
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

            if ($nextInvoiceDate) {
                $insurance->date = $nextInvoiceDate;
                $insurance->title = __('Insurance');
                $insurance->categoryIcon = 'mdi-shield-car';
                $insurance->badges = collect();

                if ($insurance->type) {
                    $insurance->badges->push([
                        'color' => 'gray',
                        'title' => config('insurances.types')[$insurance->type],
                        'icon' => match ($insurance->type) {
                            '0' => 'mdi-shield-outline',
                            '1' => 'mdi-shield-plus',
                            '2' => 'mdi-shield-star',
                            default => 'mdi-shield-car',
                        },
                    ]);
                }

                $items->push($insurance);
            }
        }

        foreach ($vehicle->taxes as $tax) {
            $nextInvoiceDate = $tax->getNextInvoiceDate($tax->start_date, $tax->end_date, $tax->invoice_day);

            if ($nextInvoiceDate) {
                $tax->date = $nextInvoiceDate;
                $tax->title = __('Tax');
                $tax->categoryIcon = 'fas-file-invoice-dollar';
                $items->push($tax);
            }
        }

        $groupedItems = $items->sortBy(function ($item) {
            return $item->date;
        })->groupBy(function ($item) {
            return Carbon::parse($item->date)->isoFormat('MMMM Y');
        });

        return $groupedItems ?? collect();
    }
}
