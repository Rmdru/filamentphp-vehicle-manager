<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\MaintenanceResource;
use App\Filament\Resources\RefuelingResource;
use App\Filament\Resources\VehicleResource;
use App\Http\Middleware\EnsureAuthenticated;
use App\Models\Vehicle;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AccountPanelProvider extends PanelProvider
{
    private function getVehicleMenuItems(): array
    {
        $menuItems = [];

        $vehicles = Vehicle::all();
        $brands = config('cars.brands');

        foreach ($vehicles as $vehicle) {
            $menuItems[] = NavigationItem::make($vehicle->full_name)
                ->label($vehicle->full_name . ' (' . $vehicle->license_plate . ')')
                ->group('My Vehicles')
                ->badge($vehicle->getStatusBadge($vehicle->id)['count'] ?: null, $vehicle->getStatusBadge($vehicle->id)['color'])
                ->url(fn (): string => route('switch-vehicle', ['vehicleId' => $vehicle->id]))
                ->isActiveWhen(fn (): bool => Session::get('vehicle_id') === $vehicle->id)
                ->icon('si-' . str($brands[$vehicle->brand])->replace(' ', '')->lower());
        }

        return $menuItems;
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('account')
            ->path('account')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->viteTheme('resources/css/filament/account/theme.css')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make()
                        ->label(__('Management'))
                        ->items([
                            ...Dashboard::getNavigationItems(),
                            ...VehicleResource::getNavigationItems(),
                            ...MaintenanceResource::getNavigationItems(),
                            ...RefuelingResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('My vehicles'))
                        ->collapsed()
                        ->items($this->getVehicleMenuItems()),
                ]);
            });
    }
}
