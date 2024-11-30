<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VehicleController extends Controller
{
    public function __invoke(Request $request, $vehicleId)
    {
        Session::put('Dashboard_filters.vehicleId', $vehicleId);

        return back();
    }
}
