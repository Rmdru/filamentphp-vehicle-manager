<?php

namespace App\Filament\Resources\TollResource\Pages;

use App\Filament\Resources\TollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTolls extends ListRecords
{
    protected static string $resource = TollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
