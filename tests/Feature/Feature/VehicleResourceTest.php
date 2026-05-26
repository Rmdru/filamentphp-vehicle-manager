<?php

declare(strict_types=1);

use App\Models\Vehicle;
use App\Filament\Resources\VehicleResource\Pages\ListVehicles;
use function Pest\Livewire\livewire;

test('it redirects to tenant registration when user has no vehicles', function () {
    Vehicle::query()->delete();

    $response = $this->get('/');

    $response->assertStatus(302);
    $response->assertRedirect(route('filament.account.tenant.registration'));
});

it('can view vehicle index page', function () {
    $tenantId = auth()->user()->getDefaultTenant()?->id;
    $vehicles = Vehicle::all();

    $response = $this->get(route('filament.account.resources.vehicles.index', ['tenant' => $tenantId]));

    $response->assertStatus(200);
    $response->assertSee(__('Vehicles'));

    livewire(ListVehicles::class)
        ->assertCanSeeTableRecords($vehicles);
});
