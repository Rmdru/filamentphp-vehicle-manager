<?php

namespace App\Filament\Resources\TollResource\Pages;

use App\Filament\Resources\TollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToll extends EditRecord
{
    protected static string $resource = TollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
