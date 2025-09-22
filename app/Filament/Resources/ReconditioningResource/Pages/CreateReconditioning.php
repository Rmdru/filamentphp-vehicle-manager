<?php

namespace App\Filament\Resources\ReconditioningResource\Pages;

use App\Filament\Resources\ReconditioningResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReconditioning extends CreateRecord
{
    protected static string $resource = ReconditioningResource::class;

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
