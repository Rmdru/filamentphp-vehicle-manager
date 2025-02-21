<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tax>
 */
class TaxFactory extends Factory
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
            'start_date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'end_date' => function ($attributes) {
                return $this->faker->dateTimeBetween($attributes['start_date'], '+1 year')->format('Y-m-d');
            },
            'price' => $this->faker->randomFloat(2, 1, 200),
            'invoice_day' => $this->faker->dayOfMonth(null),
        ];
    }
}
