<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                    ->withRefuelings(5)
            , 'vehicles')
            ->create();
    }
}
