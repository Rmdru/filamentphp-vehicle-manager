<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vignette>
 */
class VignetteFactory extends Factory
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

        return [
            'id' => $this->faker->uuid(),
            'vehicle_id' => Vehicle::factory(),
            'start_date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'end_date' => function ($attributes) {
                return $this->faker->dateTimeBetween($attributes['start_date'], '+1 year')->format('Y-m-d');
            },
            'price' => $this->faker->randomFloat(2, 1, 50),
            'country' => $this->faker->randomElement([
                null,
                $country,   
            ]),
            'areas' => $this->faker->randomElement([
                null,
                implode(', ', $this->faker->words()),
            ]),
            'comments' => $this->faker->randomElement([
                null,
                $this->faker->paragraph(),
            ]),
        ];
    }
}
