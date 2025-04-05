<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Carbon\Carbon;

;
use Illuminate\Support\Facades\Date;
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
    }
}
