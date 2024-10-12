<?php

namespace App\Filament\Resources\ParkingResource\Pages;

use App\Filament\Resources\ParkingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParking extends EditRecord
{
    protected static string $resource = ParkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
