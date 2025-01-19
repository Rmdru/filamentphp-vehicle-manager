<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Timeline;
use App\Filament\Resources\FineResource;
use App\Filament\Resources\InsuranceResource;
use App\Filament\Resources\MaintenanceResource;
use App\Filament\Resources\ParkingResource;
use App\Filament\Resources\ReconditioningResource;
use App\Filament\Resources\RefuelingResource;
use App\Filament\Resources\TaxResource;
use App\Filament\Resources\TollResource;
use App\Filament\Resources\VehicleResource;
use App\Http\Middleware\CreateFirstVehicle;
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
use Illuminate\Support\Facades\Session;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AccountPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('account')
            ->path('account')
            ->registration()
            ->login()
            ->colors([
                'primary' => Color::Blue,
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
                CreateFirstVehicle::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->viteTheme('resources/css/filament/account/theme.css')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make()
                        ->label(__('Statistics'))
                        ->items([
                            ...Dashboard::getNavigationItems(),
                            ...Timeline::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('Garage'))
                        ->items([
                            ...MaintenanceResource::getNavigationItems(),
                            ...ReconditioningResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('Fuel'))
                        ->items([
                            ...RefuelingResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('Insurance'))
                        ->items([
                            ...InsuranceResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('Tax'))
                        ->items([
                            ...TaxResource::getNavigationItems(),
                            ...ParkingResource::getNavigationItems(),
                            ...TollResource::getNavigationItems(),
                            ...FineResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make()
                        ->label(__('My vehicles'))
                        ->collapsed()
                        ->items([
                            ...VehicleResource::getNavigationItems(),
                            ...$this->getVehicleMenuItems(),
                        ]),
                ]);
            });
    }

    private function getVehicleMenuItems(): array
    {
        $menuItems = [];

        $vehicles = Vehicle::all();
        $brands = config('vehicles.brands');
        $countries = config('countries');

        foreach ($vehicles as $vehicle) {
            $menuItems[] = NavigationItem::make($vehicle->full_name)
                ->label($vehicle->full_name)
                ->group('My Vehicles')
                ->badge($vehicle->license_plate, $countries[$vehicle->country_registration]['license_plate']['filament_color'])
                ->url(fn(): string => route('switch-vehicle', ['vehicleId' => $vehicle->id]))
                ->isActiveWhen(fn(): bool => Session::get('vehicle_id') === $vehicle->id)
                ->icon('si-' . str($brands[$vehicle->brand])->replace(' ', '')->lower());
        }

        return $menuItems;
    }
}
