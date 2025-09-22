<?php

namespace App\Filament\Resources\AccidentResource\Pages;

use App\Enums\AccidentSituation;
use App\Enums\VehicleStatus;
use App\Filament\Resources\AccidentResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccident extends CreateRecord
{
    protected static string $resource = AccidentResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function creating(Vehicle $vehicle): void
    {
        $vehicle->vehicle_id = auth()->user()->vehicle_id;

        
        if (array_search(AccidentSituation::VehicleNotRollable->value, $vehicle->situation)) {
            Vehicle::update(['id' => $vehicle->vehicle_id], ['status' => VehicleStatus::NotRollable->value]);
        }

        $vehicle->total_price = ($vehicle->damage_own + $vehicle->damage_others) - ($vehicle->damage_own_insured + $vehicle->damage_others_insured);

        $vehicle->vehicle()->associate(auth()->user()->vehicle);
    }
}
