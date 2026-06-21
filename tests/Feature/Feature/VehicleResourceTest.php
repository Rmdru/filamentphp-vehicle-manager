<?php

declare(strict_types=1);

use App\Filament\Resources\VehicleResource\Pages\ListVehicles;
use App\Models\Refueling;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
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

test('it clears cached vehicle stats when a refueling is created', function () {
    $vehicle = auth()->user()->getDefaultTenant();

    $cacheKey = 'vehicle:' . $vehicle->id . ':average-fuel-consumption:' . json_encode([
        $vehicle->id,
        null,
        null,
        false,
    ]);

    Cache::put($cacheKey, 123.45, now()->addHour());

    expect(Cache::has($cacheKey))->toBeTrue();

    Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'date' => now()->toDateString(),
        'fuel_consumption' => 5.5,
        'mileage_begin' => 1000,
        'mileage_end' => 1500,
        'charge_time' => null,
        'service_by_attendant' => false,
    ]);

    expect(Cache::has($cacheKey))->toBeFalse();
});
