<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class MaintenanceController extends Controller
{
    public function __invoke(Vehicle $vehicle, string $typeMaintenance, string $date): RedirectResponse
    {
        $vehicle->maintenances()->create([
            'vehicle_id' => $vehicle->id,
            'type_maintenance' => $typeMaintenance,
            'date' => $date ?? Carbon::today()->format('Y-m-d'),
            'mileage' => Vehicle::selected()->first()->mileage_latest ?? Vehicle::selected()->first()->mileage_start,
        ]);

        return redirect()->back();
    }
}
