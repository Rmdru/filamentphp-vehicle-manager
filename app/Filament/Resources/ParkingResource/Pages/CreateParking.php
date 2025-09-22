<?php

namespace App\Filament\Resources\ParkingResource\Pages;

use App\Filament\Resources\ParkingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateParking extends CreateRecord
{
    protected static string $resource = ParkingResource::class;

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
