<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TollPaymentCircumstances;
use App\Enums\TollPaymentMethod;
use App\Enums\TollType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Toll>
 */
class TollFactory extends Factory
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
                collect(TollType::cases())->pluck('value')->toArray(),
            ),
            'payment_circumstances' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(
                    collect(TollPaymentCircumstances::cases())->pluck('value')->toArray(),
                ),
            ]),
            'payment_method' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(
                    collect(TollPaymentMethod::cases())->pluck('value')->toArray(),
                ),
            ]),
            'road' => $road = $this->faker->randomElement([
                null,
                $roadNumber,
            ]),
            'country' => $road ? $country : null,
            'road_type' => $road ? $roadTypeName : null,
            'start_location' => $this->faker->words(asText: true),
            'end_location' => $this->faker->randomElement([
                null,
                $this->faker->words(asText: true),
            ]),
            'date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'price' => $this->faker->randomFloat(2, 1, 50),
            'toll_company' => $this->faker->randomElement([
                null,
                $this->faker->company(),
            ]),
        ];
    }
}
