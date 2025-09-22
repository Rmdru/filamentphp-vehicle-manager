<?php

namespace App\Filament\Resources\TollResource\Pages;

use App\Filament\Resources\TollResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToll extends CreateRecord
{
    protected static string $resource = TollResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
