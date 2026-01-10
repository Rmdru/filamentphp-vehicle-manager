<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Services\RdwService;
use Illuminate\Console\Command;

class SyncVehicleData extends Command
{
    protected $signature = 'sync:vehicle-data';
    protected $description = 'Sync vehicle data from RDW API to vehicles database table';

    public function __construct(
        private readonly RdwService $rdwService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        Vehicle::query()
            ->whereNotNull('license_plate')
            ->chunkById(100, function ($vehicles) {
                foreach ($vehicles as $vehicle) {
                    $rdwData = json_decode($this->rdwService->fetchVehicleDataByLicensePlate($vehicle->license_plate_normalized));

                    if (! empty($rdwData)) {
                        $rdwData = $rdwData[0];
                        $vehicle->rdw_data = $rdwData[0];

                        if (! empty($rdwData['wacht_op_keuren']) && $rdwData['wacht_op_keuren'] !== 'Geen verstrekking in Open Data') {
                            $vehicle->status = VehicleStatus::Wok->value;
                        }

                        $vehicle->saveQuietly();

                        $this->info("Updated vehicle ID {$vehicle->id} with RDW data with license plate {$vehicle->license_plate}.");

                        continue;
                    }
                    
                    $this->warn("No RDW data found for vehicle ID {$vehicle->id} with license plate {$vehicle->license_plate}.");
                }
            });
    }
}
