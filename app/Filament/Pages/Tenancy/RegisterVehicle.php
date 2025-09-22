<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tenancy;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterVehicle extends RegisterTenant
{
    protected ?string $maxWidth = '5xl';

    public static function getLabel(): string
    {
        return __('Create vehicle');
    }
   

    public function getTitle(): string
    {
        return __('Create your first vehicle');
    }

    public function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return VehicleResource::form($form);
    }

    protected function handleRegistration(array $data): Vehicle
    {
        $data['user_id'] = auth()->id();

        $vehicle = Vehicle::create($data);

        return $vehicle;
    }
}