<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EnvironmentalSticker;
use App\Models\Ferry;
use App\Models\Fine;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Parking;
use App\Models\Product;
use App\Models\Reconditioning;
use App\Models\Refueling;
use App\Models\Service;
use App\Models\Tax;
use App\Models\Toll;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vignette;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->has(
                Vehicle::factory()
                    ->count(2)
                    ->has(Refueling::factory()->count(10))
                    ->has(Maintenance::factory()->count(2))
                    ->has(Insurance::factory()->count(1))
                    ->has(Reconditioning::factory()->count(2))
                    ->has(Tax::factory()->count(1))
                    ->has(Parking::factory()->count(5))
                    ->has(Toll::factory()->count(5))
                    ->has(Fine::factory()->count(2))
                    ->has(Vignette::factory()->count(2))
                    ->has(EnvironmentalSticker::factory()->count(2))
                    ->has(Ferry::factory()->count(2))
                    ->has(Product::factory()->count(5))
                    ->has(Service::factory()->count(5))
            , 'vehicles')
            ->create();
    }
}
