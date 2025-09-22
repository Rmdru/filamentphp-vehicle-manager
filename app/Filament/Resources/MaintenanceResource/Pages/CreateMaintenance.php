<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenance extends CreateRecord
{
    protected static string $resource = MaintenanceResource::class;

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
