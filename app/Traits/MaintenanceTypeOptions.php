<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\MaintenanceTypeMaintenance;

trait MaintenanceTypeOptions
{
    public function getMaintenanceTypeOptions(array $maintenanceTypes = []): array
    {
        $maintenanceTypeOptions = [];

        foreach ($maintenanceTypes as $value) {
            $maintenanceTypeOptions += [$value->value => $value->getLabel()];
        }

        return $maintenanceTypeOptions;
    }
}
