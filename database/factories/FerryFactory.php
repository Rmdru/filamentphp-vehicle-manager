<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ferry>
 */
class FerryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'vehicle_id' => Vehicle::factory(),
            'start_location' => $this->faker->words(asText: true),
            'end_location' => $this->faker->words(asText: true),
            'start_date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'end_date' => function ($attributes) {
                return $this->faker->dateTimeBetween($attributes['start_date'], '+1 day')->format('Y-m-d');
            },
            'price' => $this->faker->randomFloat(2, 0, 200),
        ];
    }
}
