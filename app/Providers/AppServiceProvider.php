<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Accident;
use App\Models\EnvironmentalSticker;
use App\Models\Ferry;
use App\Models\Fine;
use App\Models\Insurance;
use App\Models\Maintenance;
use App\Models\Parking;
use App\Models\Product;
use App\Models\Reconditioning;
use App\Models\Refueling;
use App\Models\Service;
use App\Models\Tax;
use App\Models\Toll;
use App\Models\Vehicle;
use App\Models\Vignette;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'nl', 'de', 'fr', 'it', 'es'])
                ->labels([
                    'en' => 'English (English)',
                    'nl' => 'Nederlands (Dutch)',
                    'de' => 'Deutsch (German)',
                    'fr' => 'Français (French)',
                    'it' => 'Italiano (Italian)',
                    'es' => 'Español (Spanish)',
                ])
                ->flags([
                    'en' => url('https://flagsapi.com/GB/flat/64.png'),
                    'nl' => url('https://flagsapi.com/NL/flat/64.png'),
                    'de' => url('https://flagsapi.com/DE/flat/64.png'),
                    'fr' => url('https://flagsapi.com/FR/flat/64.png'),
                    'it' => url('https://flagsapi.com/IT/flat/64.png'),
                    'es' => url('https://flagsapi.com/ES/flat/64.png'),
                ])
                ->visible(outsidePanels: true);
        });

        DB::prohibitDestructiveCommands(app()->isProduction());

        foreach ([
            Accident::class,
            EnvironmentalSticker::class,
            Ferry::class,
            Fine::class,
            Insurance::class,
            Maintenance::class,
            Parking::class,
            Product::class,
            Reconditioning::class,
            Refueling::class,
            Service::class,
            Tax::class,
            Toll::class,
            Vehicle::class,
            Vignette::class,
        ] as $model) {
            $model::saved(fn () => Cache::flush());
            $model::deleted(fn () => Cache::flush());
        }
    }
}
