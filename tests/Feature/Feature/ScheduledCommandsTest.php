<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
use App\Models\FuelDetourAggregate;
use App\Models\FuelPrice;
use App\Models\Vehicle;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;

it('registers import:vehicle-data and import:fuel-prices in the console schedule', function () {
    $events = collect(app(Schedule::class)->events());

    expect($events->contains(fn ($event) => str_contains($event->command, 'import:vehicle-data')))->toBeTrue();
    expect($events->contains(fn ($event) => str_contains($event->command, 'import:fuel-prices')))->toBeTrue();

    $vehicleDataEvent = $events->first(fn ($event) => str_contains($event->command, 'import:vehicle-data'));
    $fuelPricesEvent = $events->first(fn ($event) => str_contains($event->command, 'import:fuel-prices'));

    expect($vehicleDataEvent->expression)->toBe('0 0 * * *');
    expect($fuelPricesEvent->expression)->toBe('0 1 * * *');
});

test('import:vehicle-data command updates vehicle rdw data with mocked rdw api responses', function () {
    Vehicle::query()->delete();

    $vehicle = Vehicle::factory()->create([
        'license_plate' => 'AB-123-CD',
        'powertrain' => 'diesel',
        'tank_capacity' => 50,
    ]);

    Http::fake([
        'https://opendata.rdw.nl/resource/m9d7-ebf2.json*' => Http::response(json_encode([
            [
                'kenteken' => 'AB123CD',
                'wacht_op_keuren' => 'In afwachting',
            ],
        ]), 200),

        'https://opendata.rdw.nl/resource/t49b-isb7.json*' => Http::response(json_encode([
            [
                'referentiecode_rdw' => 'RC123',
            ],
        ]), 200),

        'https://opendata.rdw.nl/resource/j9yg-7rg9.json*' => Http::response(json_encode([
            [
                'description' => 'Test recall detail',
            ],
        ]), 200),
    ]);

    $this->artisan('import:vehicle-data')->assertExitCode(0);

    $vehicle->refresh();

    expect($vehicle->rdw_data)->toBeArray();
    expect($vehicle->rdw_data['wacht_op_keuren'])->toBe('In afwachting');
    expect($vehicle->rdw_data['open_recalls'][0]['referentiecode_rdw'])->toBe('RC123');
    expect($vehicle->status)->toBe(VehicleStatus::Wok->value);
});

test('import:fuel-prices command stores mocked dutch, belgian and german prices', function () {
    Vehicle::query()->delete();

    $vehicle = Vehicle::factory()->create([
        'powertrain' => 'diesel',
        'tank_capacity' => 50,
    ]);

    FuelDetourAggregate::query()->delete();
    FuelPrice::query()->delete();

    Http::fake([
        'https://opendata.cbs.nl/ODataApi/odata/80416ENG/TypedDataSet*' => Http::response(json_encode([
            'value' => [
                [
                    'Periods' => '2026-05-26T00:00:00',
                    'Euro95_1' => 1.65,
                    'Diesel_2' => 1.45,
                    'LPG_3' => 0.89,
                ],
            ],
        ]), 200),

        'https://carbu.com/belgie/index.php/officieleprijs*' => Http::response(
            '<table class="prix-officiel"><tbody>' .
            '<tr><td>Super 95 (E10)</td><td class="price">€ 1,74 /l</td></tr>' .
            '<tr><td>Super 98 (E5)</td><td class="price">€ 1,84 /l</td></tr>' .
            '<tr><td>Diesel (B7)</td><td class="price">€ 1,55 /l</td></tr>' .
            '<tr><td>LPG</td><td class="price">€ 0,82 /l</td></tr>' .
            '</tbody></table>',
            200,
        ),

        'https://creativecommons.tankerkoenig.de/api/v4/stats*' => Http::response(json_encode([
            'timestamp' => '2026-05-26T10:00:00Z',
            'E10' => ['median' => 1.62],
            'E5' => ['median' => 1.78],
            'Diesel' => ['median' => 1.48],
        ]), 200),
    ]);

    $this->artisan('import:fuel-prices')->assertExitCode(0);

    expect(FuelPrice::where('country', 'netherlands')->where('fuel_type', 'Unleaded 95 (E10)')->exists())->toBeTrue();
    expect(FuelPrice::where('country', 'germany')->where('fuel_type', 'Diesel')->first()->price)->toBe(1.48);
    expect(FuelDetourAggregate::where('vehicle_id', $vehicle->id)->exists())->toBeTrue();
});
