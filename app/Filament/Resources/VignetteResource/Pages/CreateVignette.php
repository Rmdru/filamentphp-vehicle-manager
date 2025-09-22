<?php

namespace App\Filament\Resources\VignetteResource\Pages;

use App\Filament\Resources\VignetteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVignette extends CreateRecord
{
    protected static string $resource = VignetteResource::class;

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
