<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Refueling;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

trait VehicleStats
{
    private function calculateAverageMonthlyCosts(bool $thisMonth = false): float
    {
        $vehicle = Filament::getTenant();
        $startDate = $this->filters['startDate'] ?? '';
        $endDate = $this->filters['endDate'] ?? '';

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $costData = $vehicle->calculateMonthlyCosts($startDate, $endDate);

        $totalCosts = 0;
        $uniqueMonths = count($costData['labels']);

        foreach ($costData['monthlyCosts'] as $costs) {
            foreach ($costs as $cost) {
                $totalCosts += $cost;
            }
        }

        return $uniqueMonths > 0 ? $totalCosts / $uniqueMonths : 0;
    }

    private function calculateCostsPerKilometer(bool $thisMonth = false): float
    {
        $averageMonthlyCosts = $this->calculateAverageMonthlyCosts();
        $currentMonthlyCosts = $this->calculateAverageMonthlyCosts(true);
        $averageMonthlyDistance = $this->calculateAverageMonthlyDistance();
        $currentMonthlyDistance = $this->calculateAverageMonthlyDistance(true);

        if ($thisMonth) {
            $rawCostsPerKilometerCurrentMonth = 0;

            if ($currentMonthlyDistance > 0) {
                $rawCostsPerKilometerCurrentMonth = $currentMonthlyCosts / $currentMonthlyDistance;
            }

            return round($rawCostsPerKilometerCurrentMonth, 3);
        }

        $rawCostsPerKilometer = 0;

        if ($averageMonthlyDistance > 0) {
            $rawCostsPerKilometer = $averageMonthlyCosts / $averageMonthlyDistance;
        }

        return round($rawCostsPerKilometer, 3);
    }

    private function calculateAverageMonthlyDistance(bool $thisMonth = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $query = Refueling::query()
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(mileage_end - mileage_begin) as total_distance')
            ->where('vehicle_id', $vehicleId);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $query->groupBy('year', 'month');

        $results = $query->get();

        $totalDistance = 0;
        $monthsCount = $results->count();

        foreach ($results as $result) {
            $totalDistance += $result->total_distance;
        }

        if ($monthsCount === 0) {
            return 0;
        }

        $averageMonthlyDistance = $totalDistance / $monthsCount;

        return round($averageMonthlyDistance);
    }

    private function calculateAverageFuelConsumption(bool $latest = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return 0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        if ($latest) {
            return round($refuelings->latest()->first()->fuel_consumption, 2);
        }

        return round($refuelings->get()->avg('fuel_consumption'), 2);
    }

    private function calculateAvgSpeed(bool $latest = false): float
    {
        $refuelings = $this->getRefuelings();

        if (empty($refuelings)) {
            return 0;
        }

        if ($latest && ! empty($refuelings->latest()->first()->avg_speed)) {
            return round($refuelings->latest()->first()->avg_speed, 1);
        }

        return round($refuelings->get()->avg('avg_speed') ?? 0, 1);
    }

    private function getRefuelings(): ?Builder
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::query()
            ->where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return null;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        return $refuelings;
    }

    private function calculateAverageRange(bool $latest = false): float
    {
        $fuelConsumption = $this->calculateAverageFuelConsumption();

        if (! $fuelConsumption) {
            return 0.0;
        }

        if ($latest) {
            $fuelConsumption = $this->calculateAverageFuelConsumption(true);
        }

        $tankCapacity = Filament::getTenant()->tank_capacity;
        $avgRange = $tankCapacity / $fuelConsumption * 100;

        return round($avgRange);
    }

    private function calculateAvgOnboardComputerDeviation(bool $latest = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $refuelings = Refueling::where('vehicle_id', $vehicleId)
            ->whereNotNull('fuel_consumption_onboard_computer');

        if (! $refuelings->count()) {
            return 0.0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        if ($latest) {
            $latestRefueling = $refuelings->latest()->first();

            if ($latestRefueling) {
                $deviation = $latestRefueling->fuel_consumption - $latestRefueling->fuel_consumption_onboard_computer;

                return round($deviation, 3);
            }

            return 0.0;
        }

        $refuelingsData = $refuelings->get();
        if ($refuelingsData->isEmpty()) {
            return 0.0;
        }

        $totalDeviation = 0.0;
        foreach ($refuelingsData as $refueling) {
            $totalDeviation += $refueling->fuel_consumption - $refueling->fuel_consumption_onboard_computer;
        }

        $averageDeviation = $totalDeviation / $refuelingsData->count();

        return round($averageDeviation, 3);
    }

    public function calculateRatioPremiumFuel(bool $thisMonth = false): float
    {
        $vehicleId = Filament::getTenant()->id;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if ($thisMonth) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        $refuelings = Refueling::where('vehicle_id', $vehicleId);

        if (! $refuelings->count()) {
            return 0.0;
        }

        if ($startDate) {
            $refuelings->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $refuelings->whereDate('date', '<=', $endDate);
        }

        $refuelingsData = $refuelings->get();
        if ($refuelingsData->isEmpty()) {
            return 0.0;
        }

        $premiumFuelTypes = [
            'Super Plus',
            'V-Power 100',
            'Ultimate 102',
            'Premium diesel',
            'Electricity AC',
        ];

        $totalAmount = 0.0;
        $premiumAmount = 0.0;

        foreach ($refuelingsData as $refueling) {
            $totalAmount += $refueling->amount;

            if (in_array($refueling->fuel_type, $premiumFuelTypes)) {
                $premiumAmount += $refueling->amount;
            }
        }

        if ($totalAmount === 0.0) {
            return 0.0;
        }

        $ratio = ($premiumAmount / $totalAmount) * 100;

        return round($ratio, 1);
    }
}