<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TollType;
use App\Enums\VehicleStatus;
use App\Models\EnvironmentalSticker;
use App\Models\Ferry;
use App\Models\Fine;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Parking;
use App\Models\Product;
use App\Models\Reconditioning;
use App\Models\Refueling;
use App\Models\Tax;
use App\Models\Toll;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vignette;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = config('vehicles.brands');
        $powertrains = trans('powertrains');
        $countries = config('countries');
        $fuelTypes = trans('fuel_types');
        $notifications = config('notifications');

        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'brand' => $this->faker->randomElement(array_keys($brands)),
            'model' => $this->faker->word(),
            'version' => $this->faker->word(),
            'engine' => $this->faker->randomElement([null, $this->faker->word()]),
            'mileage_start' => $this->faker->randomElement([
                null,
                $this->faker->numberBetween(0, 500000)
            ]),
            'mileage_latest' => function (array $attributes) {
                return $this->faker->randomElement([
                    null,
                    $this->faker->numberBetween($attributes['mileage_start'] ?? 0, 500000)
                ]);
            },
            'purchase_date' => $this->faker->randomElement([
                null,
                $this->faker->dateTimeBetween()->format('Y-m-d')
            ]),
            'construction_date' => function (array $attributes) {
                return $this->faker->dateTimeBetween(endDate: $attributes['purchase_date'] ?? 'now')->format('Y-m-d');
            },
            'purchase_price' => $this->faker->randomFloat(2, 0, 300000),
            'license_plate' => $this->faker->randomElement([
                strtoupper($this->faker->bothify('??-###-?')),
                strtoupper($this->faker->bothify('##-??-##')),
                strtoupper($this->faker->bothify('##-???-#')),
                strtoupper($this->faker->bothify('???-##-?')),
                strtoupper($this->faker->bothify('#-???-##')),
                strtoupper($this->faker->bothify('?-###-??')),
            ]),
            'powertrain' => $this->faker->randomElement(array_keys($powertrains)),
            'country_registration' => $this->faker->randomElement(array_keys($countries)),
            'is_private' => $this->faker->boolean(),
            'status' => $this->faker->randomElement(collect(VehicleStatus::cases())->pluck('value')->toArray()),
            'fuel_types' => $this->faker->randomElements(
                array: array_keys($fuelTypes),
                count: $this->faker->numberBetween(1, 3),
                allowDuplicates: false,
            ),
            'tank_capacity' => $this->faker->numberBetween(0, 1000),
            'specifications' => collect(range(1, 5))->map(function () {
                return [
                    'name' => $this->faker->word(),
                    'value' => $this->faker->randomElement([
                        $this->faker->word(),
                        $this->faker->numberBetween(0, 9999),
                        $this->faker->boolean(),
                    ]),
                    'icon' => $this->faker->randomElement([
                        'gmdi-directions-car-filled-r',
                        'gmdi-star',
                        'mdi-engine',
                        'gmdi-local-gas-station',
                        'gmdi-route',
                        'gmdi-calendar-month-r',
                        'fas-industry',
                        'gmdi-local-offer-r',
                    ]),
                ];
            })->toArray(),
            'notifications' => $notifications,
        ];
    }

    public function withRefuelings(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Refueling::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withMaintenances(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Maintenance::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withInsurances(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Insurance::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withReconditionings(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Reconditioning::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withTaxes(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Tax::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withParkings(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Parking::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withTolls(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Toll::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withFines(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Fine::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withVignettes(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Vignette::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withEnvironmentalStickers(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            EnvironmentalSticker::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withFerries(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Ferry::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }

    public function withProducts(int $count = 5): self
    {
        return $this->afterCreating(function (Vehicle $vehicle) use ($count) {
            Product::factory()->count($count)->create(['vehicle_id' => $vehicle->id]);
        });
    }
}
