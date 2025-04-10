<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccidentAttributeType;
use App\Enums\AccidentSituation;
use App\Enums\AccidentType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accident>
 */
class AccidentFactory extends Factory
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
            'type' => $this->faker->randomElement(collect(AccidentType::cases())->pluck('value')->toArray()),
            'location' => $this->faker->words(asText: true),
            'datetime' => $this->faker->dateTimeBetween()->format('Y-m-d H:i:s'),
            'description' => $this->faker->paragraph(5),
            'guilty' => $this->faker->boolean(),
            'situation' => $this->faker->randomElement(collect(AccidentSituation::cases())->pluck('value')->toArray()),
            'damage_own' => $this->faker->randomFloat(2, 0, 10000),
            'damage_own_insured' => function ($attributes) {
                return $this->faker->randomFloat(2, 0, $attributes['damage_own']);
            },
            'damage_others' => $this->faker->randomFloat(2, 0, 10000),
            'damage_others_insured' => function ($attributes) {
                return $this->faker->randomFloat(2, 0, $attributes['damage_others']);
            },
            'total_price' => function ($attributes) {
                return ($attributes['damage_own'] + $attributes['damage_others']) - ($attributes['damage_own_insured'] + $attributes['damage_others_insured']);
            },
            'attributes' => collect(range(1, 5))->map(function () {
                return [
                    'attribute_name' => $this->faker->word(),
                    'attribute_type' => $this->faker->randomElement(collect(AccidentAttributeType::cases())->pluck('value')->toArray()),
                    'attribute_situation' => $this->faker->randomElement(collect(AccidentSituation::cases())->pluck('value')->toArray()),
                    'attribute_guilty' => $this->faker->boolean(),
                    'attribute_witness' => $this->faker->boolean(),
                    'attribute_description' => $this->faker->paragraph(5),
                ];
            })->toArray(),
        ];
    }
}
