<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['powertrain'] = trans('powertrains')[$data['powertrain']] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['powertrain'] = (int)$data['powertrain'];

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
