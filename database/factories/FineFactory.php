<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FinePaymentMethod;
use App\Enums\FineProvider;
use App\Enums\FineSanction;
use App\Enums\FineType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fine>
 */
class FineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = config('countries');
        $country = $this->faker->randomElement(array_keys($countries));
        $roadTypes = $countries[$country]['road_types'];
        $roadTypeName = $this->faker->randomElement(array_keys($countries[$country]['road_types']));
        $roadType = $this->faker->randomElement($roadTypes);
        $roadPrefix = $roadType['prefix'] ?? '';
        $roadNumber = $roadPrefix . $this->faker->numberBetween(1, 999);

        return [
            'id' => $this->faker->uuid(),
            'vehicle_id' => Vehicle::factory(),
            'type' => $this->faker->randomElement(
                collect(FineType::cases())->pluck('value')->toArray(),
            ),
            'fact' => $this->faker->words(asText: true),
            'description' => $this->faker->randomElement([
                null,
                $this->faker->paragraph(),
            ]),
            'icon' => $this->faker->randomElement([
                null,
                $this->faker->randomElement([
                    'gmdi-directions-car-filled-r',
                    'gmdi-star',
                    'mdi-engine',
                    'gmdi-local-gas-station',
                    'gmdi-route',
                    'gmdi-calendar-month-r',
                    'fas-industry',
                    'gmdi-local-offer-r',
                ]),
            ]),
            'road' => $road = $this->faker->randomElement([
                null,
                $roadNumber,
            ]),
            'country' => $road ? $country : null,
            'road_type' => $road ? $roadTypeName : null,
            'road_distance_marker' => $this->faker->randomElement([
                null,
                $this->faker->randomFloat(1, 0, 500),
            ]),
            'location' => $this->faker->words(asText: true),
            'provider' => $this->faker->randomElement(
                collect(FineProvider::cases())->pluck('value')->toArray(),
            ),
            'date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'fine' => $this->faker->boolean(),
            'price' => $this->faker->randomElement([
                null,
                $this->faker->randomFloat(2, 0, 1000),
            ]),
            'payed' => $this->faker->boolean(),
            'payment_date' => $this->faker->randomElement([
                null,
                function ($attributes) {
                    $this->faker->dateTimeBetween($attributes['date'], '+1 month')->format('Y-m-d');
                },
            ]),
            'payment_method' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(
                    collect(FinePaymentMethod::cases())->pluck('value')->toArray(),
                ),
            ]),
            'sanctions' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(
                    collect(FineSanction::cases())->pluck('value')->toArray(),
                ),
            ]),
        ];
    }
}
