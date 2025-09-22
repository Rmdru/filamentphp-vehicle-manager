<?php

namespace App\Filament\Resources\FineResource\Pages;

use App\Filament\Resources\FineResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFine extends CreateRecord
{
    protected static string $resource = FineResource::class;
    
    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
