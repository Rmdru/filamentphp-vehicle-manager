<?php

declare(strict_types=1);

use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\VehicleResource\Pages\CreateVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

use function Pest\Livewire\livewire;

test('it redirects to vehicle create when user have no vehicles', function () {
    Vehicle::query()->delete();

    $response = $this->get(route('filament.account.resources.vehicles.index'));

    $response->assertStatus(302);
    $response->assertRedirect(route('filament.account.resources.vehicles.create'));
});

it('can view vehicle index page', function () {
    $vehicles = Vehicle::all();
    $response = $this->get(route('filament.account.resources.vehicles.index'));

    $response->assertStatus(200);
    $response->assertSee(__('Vehicles'));

    livewire(VehicleResource\Pages\ListVehicles::class)
        ->assertCanSeeTableRecords($vehicles);
});
