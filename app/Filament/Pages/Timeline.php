<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\View\View;

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

    protected function getViewData(): array
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

        return [
            'items' => $items,
            'groupedItems' => $groupedItems,
        ];
    }
}
