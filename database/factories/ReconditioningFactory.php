<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReconditioningExecutor;
use App\Enums\ReconditioningType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reconditioning>
 */
class ReconditioningFactory extends Factory
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
            'date' => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'type' => $this->faker->randomElement(collect(ReconditioningType::cases())->pluck('value')->toArray()),
            'executor' => $this->faker->randomElement(collect(ReconditioningExecutor::cases())->pluck('value')->toArray()),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'location' => $this->faker->words(asText: true),
            'description' => $this->faker->paragraphs(asText: true),
        ];
    }
}
