<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\Vehicles;
use Illuminate\Support\Facades\Http;

class RdwService
{
    public function fetchVehicleDataByLicensePlate(string $licensePlate): string
    {
        return $this->call('m9d7-ebf2.json', ['kenteken' => $licensePlate]);
    }

    public function fetchFuelDataByLicensePlate(string $licensePlate): string
    {
        return $this->call('8ys7-d773.json', ['kenteken' => $licensePlate]);
    }

    public function fetchOpenRecallsByLicensePlate(string $licensePlate): string
    {
        return $this->call('t49b-isb7.json', [
            'kenteken' => $licensePlate,
            'code_status' => 'O',
        ]);
    }

    public function fetchRecallByReferenceCode(string $referenceCodeRdw): string
    {
        return $this->call('j9yg-7rg9.json', ['referentiecode_rdw' => $referenceCodeRdw]);
    }
    
    public function getPowertrainOptionFromRdwFuelResponse(string $rdwFuelType): string
    {
        $mapping = [
            'benzine' => 'gasoline',
            'diesel' => 'diesel',
            'elektriciteit' => 'electricity',
            'waterstof' => 'hydrogen',
            'lpg' => 'gasoline_lpg',
            'cng' => 'gasoline_cng',
        ];

        if (isset($mapping[$rdwFuelType])) {
            return $mapping[$rdwFuelType];
        }

        return '';
    }

    public function getOpenRecalls(string $licensePlate): array
    {        
        $recalls = [];
        $openRecallsRdw = json_decode((new RdwService)->fetchOpenRecallsByLicensePlate($licensePlate), true);

        foreach ($openRecallsRdw as $openRecallRdw) {
            $referenceCodeRdw = $openRecallRdw['referentiecode_rdw'] ?? '';

            if (empty($referenceCodeRdw)) {
                continue;
            }

            $rdwRecallDetails = json_decode((new RdwService)->fetchRecallByReferenceCode($referenceCodeRdw), true);

            if (! empty($rdwRecallDetails)) {
                $recalls[] = array_merge($openRecallRdw, $rdwRecallDetails[0]);
            }
        }

        return $recalls;
    }

    private function call(string $endpoint, array $params): string
    {
        $response = Http::timeout(5)
            ->retry(3, 100)
            ->get(config('rdw.base_url') . $endpoint, $params);

        if ($response->successful()) {
            return $response->body();
        }

        return '';
    }
}