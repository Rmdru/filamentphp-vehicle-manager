<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Vehicle;

trait InsuranceTypeOptions
{
    public function getInsuranceTypeOptions(): array
    {
        $insuranceTypes = config('insurances.types');
        $insuranceTypeOptions = [];

        foreach ($insuranceTypes as $key => $value) {
            $insuranceTypeOptions[$key] = $value['name'];
        }

        return $insuranceTypeOptions;
    }
}
