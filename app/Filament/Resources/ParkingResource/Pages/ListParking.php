<?php

namespace App\Filament\Resources\ParkingResource\Pages;

use App\Filament\Resources\ParkingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParking extends ListRecords
{
    protected static string $resource = ParkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
