<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\ImportFuelPrices\Netherlands;
use App\Pipelines\ImportFuelPrices\Belgium;
use App\Pipelines\ImportFuelPrices\CalculateFuelDetourAggregates;
use App\Pipelines\ImportFuelPrices\Germany;
use App\Pipelines\ImportFuelPrices\Store;
use Illuminate\Support\Facades\Log;

class ImportFuelPrices extends Command
{
    protected $signature = 'import:fuel-prices';

    protected $description = 'Import fuel prices';

    public function handle(): void
    {
        try {
            $data = [];

            app(Pipeline::class)
                ->send($data)
                ->through([
                    Netherlands::class,
                    Belgium::class,
                    Germany::class,
                    Store::class,
                    CalculateFuelDetourAggregates::class,
                ])
                ->thenReturn();

            Log::channel('fuel-prices')->info('Fuel prices imported successfully');
            $this->info('Fuel prices imported successfully');
        } catch (\Throwable $e) {
            Log::channel('fuel-prices')->error('Fuel prices import failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->error('Fuel prices import failed: ' . $e->getMessage());
        }
    }
}
