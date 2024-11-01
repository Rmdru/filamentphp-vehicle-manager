<?php

namespace App\Filament\Resources\FineResource\Pages;

use App\Filament\Resources\FineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFine extends EditRecord
{
    protected static string $resource = FineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
