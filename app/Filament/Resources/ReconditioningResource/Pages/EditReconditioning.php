<?php

namespace App\Filament\Resources\ReconditioningResource\Pages;

use App\Filament\Resources\ReconditioningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReconditioning extends EditRecord
{
    protected static string $resource = ReconditioningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
