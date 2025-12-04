<?php

declare(strict_types=1);

use App\Console\Commands\SyncVehicleData;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SyncVehicleData::class)->daily();
