<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ParkingPaymentMethod;
use App\Enums\ParkingType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parking>
 */
class ParkingFactory extends Factory
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
            'type' => $this->faker->randomElement(collect(ParkingType::cases())->pluck('value')->toArray()),
            'location' => $this->faker->words(asText: true),
            'company' => $this->faker->company(),
            'start_time' =>$this->faker->dateTimeBetween()->format('Y-m-d H:i:s'),
            'end_time' => function ($attributes) {
                return $this->faker->dateTimeBetween($attributes['start_time'], '+1 day')->format('Y-m-d H:i:s');
            },
            'price' => $this->faker->randomFloat(2, 1, 50),
            'payment_method' => $this->faker->randomElement(collect(ParkingPaymentMethod::cases())->pluck('value')->toArray()),
        ];
    }
}
