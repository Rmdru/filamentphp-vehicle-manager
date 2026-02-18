<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Services\RdwService;
use Illuminate\Console\Command;

class ImportVehicleData extends Command
{
    protected $signature = 'import:vehicle-data';
    protected $description = 'Import vehicle data from RDW API to vehicles database table';

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
                    $rdwData = json_decode($this->rdwService->fetchVehicleDataByLicensePlate($vehicle->license_plate_normalized), true);

                    if (empty($rdwData)) {
                        $this->warn("No RDW data found for vehicle ID {$vehicle->id} with license plate {$vehicle->license_plate}.");

                        continue;
                    }
                    
                    $rdwData = $rdwData[0];

                    $rdwData['open_recalls'] = $this->rdwService->getOpenRecalls($vehicle->license_plate_normalized);

                    $vehicle->rdw_data = $rdwData;

                    if (! empty($rdwData['wacht_op_keuren']) && $rdwData['wacht_op_keuren'] !== 'Geen verstrekking in Open Data') {
                        $vehicle->status = VehicleStatus::Wok->value;
                    }

                    $vehicle->saveQuietly();

                    $this->info("Updated vehicle ID {$vehicle->id} with RDW data with license plate {$vehicle->license_plate}.");
                }
            });
    }
}
