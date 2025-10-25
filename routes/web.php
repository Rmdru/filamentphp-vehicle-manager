<?php

declare(strict_types=1);

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect(route('filament.account.auth.login'));
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect(route('filament.account.pages.dashboard', [
            'tenant' => auth()->user()->getDefaultTenant()->id,
        ]));
    });

    Route::get('/account/complete-small-check/{vehicle}/{typeMaintenance}/{date}', MaintenanceController::class)
        ->name('complete-small-check');

    Route::get('/account/vehicles/{vehicle}/image', [VehicleController::class, 'image'])
        ->name('vehicle.image');
});
