<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ServiceType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
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
            'type' => $this->faker->randomElement(collect(ServiceType::cases())->pluck('value')->toArray()),
            'name' => $this->faker->words(asText: true),
            'date' =>$this->faker->dateTimeBetween()->format('Y-m-d'),
            'price' => $this->faker->randomFloat(2, 1, 50),
            'comments' => $this->faker->randomElement([
                null,
                $this->faker->sentences(asText: true),
            ]),
        ];
    }
}
