<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Component;

class StatusNotification extends Component
{
    public string $vehicleId;
    private array $notifications = [];

    public function mount($vehicleId): void
    {
        $this->vehicleId = $vehicleId;

        $this->getInsuranceNotification();
        $this->getApkNotification();
        $this->getMaintenanceNotification();
        $this->getAircoCheckNotification();
        $this->getRefuelingNotification();
        $this->getEverythingOkNotification();
    }

    public function getInsuranceNotification()
    {
        $timeTillInsurance = Vehicle::selected()->latest()->first()->insurance_status['time'] ?? null;

        if ($this->vehicleId) {
            $timeTillInsurance = Vehicle::where('id', $this->vehicleId)->latest()->first()->insurance_status['time'] ?? null;
        }

        if (! $timeTillInsurance) {
            $this->createNotification('critical', __('No insurance found! Your are currently not allowed to drive with the vehicle!'), 'fas-hands-holding-circle');
            return null;
        }

        if ($timeTillInsurance < 31) {
            $this->createNotification('critical', __('Insurance expires within 1 month!'), 'fas-hands-holding-circle');
            return null;
        }

        if ($timeTillInsurance < 62) {
            $this->createNotification('warning', __('Insurance expires within 2 months!'), 'fas-hands-holding-circle');
            return null;
        }
    }

    private function createNotification(string $type = '', string $text = '', string $categoryIcon = ''): void
    {
        $types = [
            'critical' => [
                'textColor' => 'text-red-500',
                'borderColor' => 'border-red-800',
                'icon' => 'gmdi-warning-r',
            ],
            'warning' => [
                'textColor' => 'text-orange-400',
                'borderColor' => 'border-orange-500',
                'icon' => 'gmdi-warning-r',
            ],
            'success' => [
                'textColor' => 'text-green-500',
                'borderColor' => 'border-green-800',
                'icon' => 'gmdi-check-r',
            ],
        ];

        $this->notifications[] = array_merge($types[$type], [
            'categoryIcon' => $categoryIcon,
            'text' => $text,
        ]);
    }

    private function getApkNotification()
    {
        $timeTillApk = Vehicle::selected()->latest()->first()->apk_status['time'] ?? null;

        if ($this->vehicleId) {
            $timeTillApk = Vehicle::where('id', $this->vehicleId)->latest()->first()->apk_status['time'] ?? null;
        }

        if (! $timeTillApk) {
            return null;
        }

        if ($timeTillApk < 1) {
            $this->createNotification('critical', __('MOT expired! Your are currently not allowed to drive with the vehicle!'), 'gmdi-security');
            return null;
        }

        if ($timeTillApk < 31) {
            $this->createNotification('critical', __('MOT expires within 1 month!'), 'gmdi-security');
            return null;
        }

        if ($timeTillApk < 62) {
            $this->createNotification('warning', __('MOT expires within 2 months!'), 'gmdi-security');
            return null;
        }

        return null;
    }

    private function getMaintenanceNotification(): null
    {
        $maintenanceStatus = Vehicle::selected()->latest()->first()->maintenance_status;

        if ($this->vehicleId) {
            $maintenanceStatus = Vehicle::where('id', $this->vehicleId)->latest()->first()->maintenance_status;
        }

        if (! $maintenanceStatus) {
            return null;
        }

        if ($maintenanceStatus['time'] < 31 || $maintenanceStatus['distance'] < 1500) {
            $this->createNotification('critical', __('Maintenance required now'), 'mdi-car-wrench');
            return null;
        }

        if ($maintenanceStatus['time'] < 62 || $maintenanceStatus['distance'] < 3000) {
            $this->createNotification('warning', __('Maintenance required soon'), 'mdi-car-wrench');
            return null;
        }

        return null;
    }

    private function getAircoCheckNotification()
    {
        $timeTillAiroCheck = Vehicle::selected()->latest()->first()->airco_check_status['time'] ?? null;

        if ($this->vehicleId) {
            $timeTillAiroCheck = Vehicle::where('id', $this->vehicleId)->latest()->first()->airco_check_status['time'] ?? null;
        }

        if (! $timeTillAiroCheck) {
            return null;
        }

        if ($timeTillAiroCheck < 31) {
            $this->createNotification('critical', __('Airco check required!'), 'mdi-air-conditioner');
            return null;
        }

        if ($timeTillAiroCheck < 62) {
            $this->createNotification('warning', __('Airco check required soon!'), 'mdi-air-conditioner');
            return null;
        }
    }

    private function getRefuelingNotification(): null
    {
        $selectedVehicle = Vehicle::selected()->latest()->first();

        if ($this->vehicleId) {
            $selectedVehicle = Vehicle::where('id', $this->vehicleId)->latest()->first();
        }

        $timeTillRefueling = $selectedVehicle->fuel_status;
        $refuelingsCount = $selectedVehicle->refuelings->count();

        if (! $timeTillRefueling && ! $refuelingsCount) {
            return null;
        }

        if ($timeTillRefueling < 10) {
            $this->createNotification('critical', __('Fuel is too old!'), 'gmdi-local-gas-station-r');
            return null;
        }

        if ($timeTillRefueling < 30) {
            $this->createNotification('warning', __('Fuel is getting old!'), 'gmdi-local-gas-station-r');
            return null;
        }

        return null;
    }

    private function getEverythingOkNotification(): void
    {
        if (empty($this->notifications)) {
            $this->createNotification('success', __('Everything ok! Vrooooooom'));
        }
    }

    public function render(): View
    {
        return view('livewire.status-notification', [
            'notifications' => $this->notifications,
        ]);
    }
}
