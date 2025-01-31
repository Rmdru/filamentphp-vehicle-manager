<?php

namespace App\Filament\Resources\VignetteResource\Pages;

use App\Filament\Resources\VignetteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVignettes extends ListRecords
{
    protected static string $resource = VignetteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
