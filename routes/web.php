<?php

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use Filament\Facades\Filament;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect(route('filament.account.pages.dashboard', ['tenant' => Filament::getTenant()]));
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect(route('filament.account.pages.dashboard', [
            'tenant' => auth()->user()->getDefaultTenant(Filament::getPanel('account'))->id,
        ]));
    });
    Route::get('/account/switch-vehicle/{vehicleId}', [VehicleController::class, 'switchVehicle'])
        ->name('switch-vehicle');

    Route::get('/account/complete-small-check/{vehicle}/{typeMaintenance}/{date}', MaintenanceController::class)
        ->name('complete-small-check');

    Route::get('/account/vehicles/{vehicle}/image', [VehicleController::class, 'image'])
        ->name('vehicle.image');
});
