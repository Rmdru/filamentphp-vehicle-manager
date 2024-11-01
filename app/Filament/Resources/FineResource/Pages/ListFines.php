<?php

namespace App\Filament\Resources\FineResource\Pages;

use App\Filament\Resources\FineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFines extends ListRecords
{
    protected static string $resource = FineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
