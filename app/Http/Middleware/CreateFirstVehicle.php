<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateFirstVehicle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->user()
            && ! Vehicle::count()
            && ! $request->routeIs('filament.account.resources.vehicles.create')
        ) {
            return redirect()->route('filament.account.resources.vehicles.create');
        }

        return $next($request);
    }
}
