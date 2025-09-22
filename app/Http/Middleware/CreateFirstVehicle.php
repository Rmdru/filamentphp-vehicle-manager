<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
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
            && ! User::isOnboarded()
            && ! $request->routeIs('filament.account.tenant.registration')
        ) {
            return redirect()->route('filament.account.tenant.registration');
        }

        return $next($request);
    }
}
