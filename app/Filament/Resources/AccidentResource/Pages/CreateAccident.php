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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_search(AccidentSituation::VehicleNotRollable->value, $data['situation'])) {
            Vehicle::update(['id' => $data['vehicle_id']], ['status' => VehicleStatus::NotRollable->value]);
        }

        $data['total_price'] = ($data['damage_own'] + $data['damage_others']) - ($data['damage_own_insured'] + $data['damage_others_insured']);

        return $data;
    }
}
