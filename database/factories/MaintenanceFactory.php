<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MaintenancePaymentMethod;
use App\Enums\MaintenanceTypeMaintenance;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
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
            'garage' => $this->faker->randomElement([
                null,
                $this->faker->words(asText: true),
            ]),
            'type_maintenance' => $this->faker->randomElement(collect(MaintenanceTypeMaintenance::cases())->pluck('value')->toArray()),
            'apk' => $this->faker->boolean(),
            'apk_date' => function (array $attributes) {
                if ($attributes['apk']) {
                    return $this->faker->dateTimeBetween($attributes['date'], '+5 years')->format('Y-m-d');
                }

                return null;
            },
            'description' => $this->faker->randomElement([
                null,
                $this->faker->paragraphs(asText: true),
            ]),
            'total_price' => $this->faker->randomFloat(2, 1, 1000),
            'mileage' => $this->faker->numberBetween(0, 500000),
            'tasks' => collect(range(1, 5))->map(function () {
                return [
                    'task' => $this->faker->words(asText: true),
                    'price' => $this->faker->randomFloat(2, 1, 1000),
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
            'payment_method' => $this->faker->randomElement([
                null,
                $this->faker->randomElement(collect(MaintenancePaymentMethod::cases())->pluck('value')->toArray()),
            ]),
        ];
    }
}
