<?php

declare(strict_types=1);

use App\Models\Vehicle;
use App\Filament\Resources\VehicleResource\Pages\ListVehicles;
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

    livewire(ListVehicles::class)
        ->assertCanSeeTableRecords($vehicles);
});
