<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Filament\Resources\RefuelingResource;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRefueling extends CreateRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $vehicle = Filament::getTenant();

        $distance = $data['mileage_end'] - $data['mileage_begin'];

        $data['fuel_consumption'] = round(($data['amount'] / $distance) * 100, 2);
        $data['costs_per_kilometer'] = round($data['total_price'] / $distance, 2);

        if ($vehicle->mileage_latest < $data['mileage_end']) {
            $vehicle->update(['mileage_latest' => $data['mileage_end']]);
        }

        return $data;
    }
}
