<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Filament\Resources\RefuelingResource;
use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditRefueling extends EditRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $vehicle = Vehicle::find(Filament::getTenant()->id);

        $distance = $data['mileage_end'] - $data['mileage_begin'];

        $data['fuel_consumption'] = round(($data['amount'] / $distance) * 100, 2);
        $data['costs_per_kilometer'] = round($data['total_price'] / $distance, 2);

        if ($vehicle->mileage_latest < $data['mileage_end']) {
            $vehicle->update(['mileage_latest' => $data['mileage_end']]);
        }

        return $data;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
