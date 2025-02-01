<?php

namespace App\Filament\Resources\EnvironmentalStickerResource\Pages;

use App\Filament\Resources\EnvironmentalStickerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalStickers extends ListRecords
{
    protected static string $resource = EnvironmentalStickerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
