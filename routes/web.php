<?php

declare(strict_types=1);

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Models\Vehicle;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect(route('filament.account.auth.login'));
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        if (empty(auth()->user()->getDefaultTenant()?->id) && empty(Vehicle::ownVehicles()->latest()->first()?->id)) {
            return redirect(route('filament.account.tenant.registration'));
        }

        return redirect(route('filament.account.pages.dashboard', [
            'tenant' => auth()->user()->getDefaultTenant()?->id ?? Vehicle::ownVehicles()->latest()->first()?->id,
        ]));
    });

    Route::get('/account/{vehicle}/complete-small-check/{typeMaintenance}/{date}', MaintenanceController::class)
        ->name('complete-small-check');

    Route::get('/account/{vehicle}/image', [VehicleController::class, 'image'])
        ->name('vehicle.image');
});
