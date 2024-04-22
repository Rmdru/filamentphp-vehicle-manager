<?php

namespace App\Filament\Resources\RefuelingResource\Pages;

use App\Filament\Resources\RefuelingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRefueling extends EditRecord
{
    protected static string $resource = RefuelingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
