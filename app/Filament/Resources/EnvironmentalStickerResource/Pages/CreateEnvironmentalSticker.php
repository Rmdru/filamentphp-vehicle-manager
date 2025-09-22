<?php

namespace App\Filament\Resources\EnvironmentalStickerResource\Pages;

use App\Filament\Resources\EnvironmentalStickerResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;

class CreateEnvironmentalSticker extends CreateRecord
{
    protected static string $resource = EnvironmentalStickerResource::class;
    
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
