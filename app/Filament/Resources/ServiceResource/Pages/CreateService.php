<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
