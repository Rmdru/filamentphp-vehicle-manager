<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Filament\Resources\RefuelingResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRefueling extends EditRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $distance = $data['mileage_end'] - $data['mileage_begin'];
        $data['fuel_consumption'] = round($data['amount'] / $distance * 100, 2);
        $data['costs_per_kilometer'] = round($data['amount'] / $distance, 2);

        $newLatestVehicleMileage = max(Vehicle::where('id', $data['vehicle_id'])->first()->mileage_latest, $data['mileage_end']);

        Vehicle::where('id', $data['vehicle_id'])->update(['mileage_latest' => $newLatestVehicleMileage]);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
