<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'gmdi-bar-chart-r';

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }
    public static function getModelLabel(): string
    {
        return __('Dashboard');
    }
}
