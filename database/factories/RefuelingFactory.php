<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RefuelingClimateControl;
use App\Enums\RefuelingDrivingStyle;
use App\Enums\RefuelingPaymentMethod;
use App\Enums\RefuelingRoutes;
use App\Enums\RefuelingTires;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Refueling>
 */
class RefuelingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = config('countries');
        $gasStationBrands = config('refuelings.gas_station_logos');
        $fuelTypes = trans('fuel_types');

        $country = $this->faker->randomElement(array_keys($countries));
        $roadTypes = $countries[$country]['road_types'];
        $roadType = $this->faker->randomElement($roadTypes);
        $roadPrefix = $roadType['prefix'] ?? '';
        $roadNumber = $roadPrefix . $this->faker->numberBetween(1, 999);

        return [
            'id' => $this->faker->uuid(),
            'vehicle_id' => Vehicle::factory(),
            'date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'country' => $country,
            'gas_station' => str($this->faker->randomElement(array_keys($gasStationBrands)))->ucfirst() . ' ' . $roadNumber . ' ' . $this->faker->word(),
            'fuel_type' => $this->faker->randomElement(array_keys($fuelTypes)),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'unit_price' => $this->faker->randomFloat(3, 1, 2),
            'total_price' => $this->faker->randomFloat(2, 1, 200),
            'mileage_begin' => $this->faker->numberBetween(0, 500000),
            'mileage_end' => function (array $attributes) {
                return $this->faker->numberBetween($attributes['mileage_begin'] + 200, $attributes['mileage_begin'] + 1500);
            },
            'fuel_consumption_onboard_computer' => $this->faker->randomElement([
                null,
                $this->faker->randomFloat(1, 1, 10),
            ]),
            'fuel_consumption' => $this->faker->randomFloat(2, 1, 10),
            'costs_per_kilometer' => $this->faker->randomFloat(2, 0, 0.5),
            'tires' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(collect(RefuelingTires::cases())->pluck('value')->toArray()),
            ]),
            'climate_control' => $this->faker->randomElement([
                null,
                $this->faker->randomElements(
                    array: collect(RefuelingClimateControl::cases())->pluck('value')->toArray(),
                    count: $this->faker->numberBetween(1, count(RefuelingClimateControl::cases())),
                    allowDuplicates: false,
                ),
            ]),
            'routes' => $this->faker->randomElement([
                null,
                $this->faker->randomElements(
                    array: collect(RefuelingRoutes::cases())->pluck('value')->toArray(),
                    count: $this->faker->numberBetween(1, count(RefuelingRoutes::cases())),
                    allowDuplicates: false,
                ),
            ]),
            'driving_style' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(collect(RefuelingDrivingStyle::cases())->pluck('value')->toArray()),
            ]),
            'avg_speed' => $this->faker->randomElement([
                null,
                $this->faker->randomFloat(0, 1, 200),
            ]),
            'discount' => $this->faker->randomElement([
                null,
                $this->faker->sentence(),
            ]),
            'payment_method' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(collect(RefuelingPaymentMethod::cases())->pluck('value')->toArray()),
            ]),
            'charge_time' => $this->faker->randomElement([
                null,
                $this->faker->randomFloat(0, 1, 720),
            ]),
            'service_by_attendant' => $this->faker->randomElement([
                null,
                $this->faker->boolean(),
            ]),
            'comments' => $this->faker->randomElement([
                null,
                $this->faker->sentences(asText: true),
            ]),
        ];
    }
}
