<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'nl', 'de', 'it', 'fr', 'es'])
                ->labels([
                    'en' => 'English (English)',
                    'nl' => 'Nederlands (Dutch)',
                    'de' => 'Deutsch (German)',
                    'it' => 'Italiano (Italian)',
                    'fr' => 'Français (French)',
                    'es' => 'Español (Spanish)',
                ])
                ->flags([
                    'en' => url('https://flagsapi.com/GB/flat/64.png'),
                    'nl' => url('https://flagsapi.com/NL/flat/64.png'),
                    'de' => url('https://flagsapi.com/DE/flat/64.png'),
                    'it' => url('https://flagsapi.com/IT/flat/64.png'),
                    'fr' => url('https://flagsapi.com/FR/flat/64.png'),
                    'es' => url('https://flagsapi.com/ES/flat/64.png'),
                ]);
        });
    }
}
