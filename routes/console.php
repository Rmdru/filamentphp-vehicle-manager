<?php

declare(strict_types=1);

use App\Console\Commands\ImportFuelPrices;
use App\Console\Commands\ImportVehicleData;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ImportVehicleData::class)->daily();
Schedule::command(ImportFuelPrices::class)->daily()->at('01:00');
