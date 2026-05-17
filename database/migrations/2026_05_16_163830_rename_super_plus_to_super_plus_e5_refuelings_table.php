<?php

declare(strict_types=1);

use App\Models\Refueling;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Refueling::query()
            ->where('fuel_type', 'Super Plus')
            ->update(['fuel_type' => 'Super Plus (E5)']);

        Vehicle::query()
            ->whereJsonContains('fuel_types', 'Super Plus')
            ->get()
            ->each(function (Vehicle $vehicle) {
                $fuelTypes = $vehicle->fuel_types;
                $index = array_search('Super Plus', $fuelTypes);
                if ($index !== false) {
                    $fuelTypes[$index] = 'Super Plus (E5)';
                    $vehicle->update(['fuel_types' => $fuelTypes]);
                }
            });
    }

    public function down(): void
    {
        Refueling::query()
            ->where('fuel_type', 'Super Plus (E5)')
            ->update(['fuel_type' => 'Super Plus']);

        Vehicle::query()
            ->whereJsonContains('fuel_types', 'Super Plus (E5)')
            ->get()
            ->each(function (Vehicle $vehicle) {
                $fuelTypes = $vehicle->fuel_types;
                $index = array_search('Super Plus (E5)', $fuelTypes);
                if ($index !== false) {
                    $fuelTypes[$index] = 'Super Plus';
                    $vehicle->update(['fuel_types' => $fuelTypes]);
                }
            });
    }
};
