<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
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
                    ->withRefuelings(10)
                    ->withMaintenances(2)
                    ->withInsurances(1)
                    ->withReconditionings(2)
                    ->withTaxes(1)
                    ->withParkings(5)
                    ->withTolls(5)
                    ->withFines(2)
                    ->withVignettes(2)
                    ->withEnvironmentalStickers(2)
                    ->withFerries(2)
            , 'vehicles')
            ->create();
    }
}
