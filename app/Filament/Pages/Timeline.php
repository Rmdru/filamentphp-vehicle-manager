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

    public function getTitle(): string | Htmlable
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

        $vehicles = Vehicle::selected()
            ->with([
                'maintenances',
                'refuelings',
            ])
            ->latest()
            ->first();

        foreach ($vehicles->maintenances as $maintenance) {
            $maintenance->icon = ! $maintenance->type_maintenance && $maintenance->apk ? 'gmdi-security' : 'mdi-car-wrench';
        }

        foreach ($vehicles->refuelings as $refueling) {
            $gasStationBrand = str($refueling->gas_station)->lower()->explode(' ')[0];

            $refueling->icon = $gasStationLogos[$gasStationBrand] ?? $gasStationLogos['default'];
            $refueling->fuel_type = $fuelTypes[$refueling->fuel_type];
        }

        $items = $vehicles->maintenances->merge($vehicles->refuelings)->sortByDesc('date');

        $groupedItems = $items->groupBy(function($item) {
            return Carbon::createFromFormat('Y-m', $item->date->format('Y-m'))->isoFormat('MMMM Y');
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
            ->latest()
            ->first();

        $items = collect();
        $apk = Maintenance::find($vehicle->apk);
        $maintenance = Maintenance::find($vehicle->maintenance);

        if (! empty($apk)) {
            $apk->icon = 'gmdi-security';
            $apk->date = $apk->date->addYear();
        }

        if (! empty($maintenance)) {
            $maintenance->icon = 'mdi-car-wrench';
            $maintenance->date = $maintenance->date->addYear();
        }

        if (! empty($apk) && ! empty($maintenance)) {

            $items->push($apk, $maintenance);

            $groupedItems = $items->groupBy(function($item) {
                return Carbon::createFromFormat('Y-m', $item->date->format('Y-m'))->isoFormat('MMMM Y');
            });
        }

        return $groupedItems ?? collect();
    }
}
