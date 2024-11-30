<?php

namespace App\Filament\Resources\ReconditioningResource\Pages;

use App\Filament\Resources\ReconditioningResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReconditionings extends ListRecords
{
    protected static string $resource = ReconditioningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
