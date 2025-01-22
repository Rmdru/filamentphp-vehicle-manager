<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Component;

class StatusNotification extends Component
{
    private array $notifications = [];

    public function mount(): void
    {
        $vehicle = Vehicle::selected()->first();

        if (! empty($vehicle->notifications['insurance']['status'])) {
            $this->getInsuranceNotification($vehicle);
        }

        if (! empty($vehicle->notifications['tax']['period_reminder'])) {
            $this->getTaxNotification($vehicle);
        }

        if (! empty($vehicle->notifications['maintenance']['apk'])) {
            $this->getApkNotification($vehicle);
        }

        if (! empty($vehicle->notifications['maintenance']['maintenance'])) {
            $this->getMaintenanceNotification($vehicle);
        }

        if (! empty($vehicle->notifications['maintenance']['airco_check'])) {
            $this->getAircoCheckNotification($vehicle);
        }

        if (! empty($vehicle->notifications['refueling']['old_fuel'])) {
            $this->getRefuelingNotification($vehicle);
        }

        if (! empty($vehicle->notifications['reconditioning']['washing'])) {
            $this->getWashingNotification($vehicle);
        }

        if (! empty($vehicle->notifications['maintenance']['tire_pressure_check'])) {
            $this->getTirePressureNotification($vehicle);
        }

        if (! empty($vehicle->notifications['maintenance']['liquids_check'])) {
            $this->getLiquidsCheckNotification($vehicle);
        }

        $this->getEverythingOkNotification();
    }

    private function getInsuranceNotification(Vehicle $vehicle): void
    {
        $timeTillInsurance = $vehicle->insurance_status['time'] ?? null;

        if (! $timeTillInsurance) {
            $this->createNotification('critical', __('No insurance found! Your are currently not allowed to drive with the vehicle!'), 'mdi-shield-car');
            return;
        }

        if ($timeTillInsurance < 31) {
            $this->createNotification('warning', __('Insurance expires within 1 month!'), 'mdi-shield-car');
            return;
        }

        if ($timeTillInsurance < 62) {
            $this->createNotification('info', __('Insurance expires within 2 months!'), 'mdi-shield-car');
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
            'info' => [
                'textColor' => 'text-blue-400',
                'borderColor' => 'border-blue-500',
                'icon' => 'gmdi-info-r',
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

    private function getTaxNotification(Vehicle $vehicle): void
    {
        $timeTillTax = $vehicle->tax_status['time'] ?? null;

        if ($timeTillTax > 0 && $timeTillTax < 31) {
            $this->createNotification('info', __('New tax period within 1 month!'), 'mdi-highway');
        }
    }

    private function getApkNotification(Vehicle $vehicle): void
    {
        $timeTillApk = $vehicle->apk_status['time'] ?? null;

        if (! $timeTillApk) {
            return;
        }

        if ($timeTillApk < 1) {
            $this->createNotification('critical', __('MOT expired! Your are currently not allowed to drive with the vehicle!'), 'gmdi-security');
            return;
        }

        if ($timeTillApk < 31) {
            $this->createNotification('critical', __('MOT expires within 1 month!'), 'gmdi-security');
            return;
        }

        if ($timeTillApk < 62) {
            $this->createNotification('warning', __('MOT expires within 2 months!'), 'gmdi-security');
        }
    }

    private function getMaintenanceNotification(Vehicle $vehicle): void
    {
        $maintenanceStatus = $vehicle->maintenance_status;

        if (! $maintenanceStatus) {
            return;
        }

        if ($maintenanceStatus['time'] < 31 || $maintenanceStatus['distance'] < 1500) {
            $this->createNotification('critical', __('Maintenance required now'), 'mdi-car-wrench');
            return;
        }

        if ($maintenanceStatus['time'] < 62 || $maintenanceStatus['distance'] < 3000) {
            $this->createNotification('warning', __('Maintenance required soon'), 'mdi-car-wrench');
        }
    }

    private function getAircoCheckNotification(Vehicle $vehicle): void
    {
        $timeTillAircoCheck = $vehicle->airco_check_status['time'] ?? null;

        if (! $timeTillAircoCheck) {
            return;
        }

        if ($timeTillAircoCheck < 31) {
            $this->createNotification('critical', __('Airco check required!'), 'mdi-air-conditioner');
            return;
        }

        if ($timeTillAircoCheck < 62) {
            $this->createNotification('warning', __('Airco check required soon!'), 'mdi-air-conditioner');
        }
    }

    private function getRefuelingNotification(Vehicle $vehicle): void
    {
        $timeTillRefueling = $vehicle->fuel_status;

        if (empty($timeTillRefueling)) {
            return;
        }

        if ($timeTillRefueling < 10) {
            $this->createNotification('critical', __('Fuel is too old!'), 'gmdi-local-gas-station-r');
            return;
        }

        if ($timeTillRefueling < 30) {
            $this->createNotification('warning', __('Fuel is getting old!'), 'gmdi-local-gas-station-r');
        }
    }

    private function getWashingNotification(Vehicle $vehicle): void
    {
        $timeTillWash = $vehicle->washing_status['time'];

        if (! isset($timeTillWash)) {
            return;
        }

        if ($timeTillWash < 5) {
            $this->createNotification('warning', __('Washing required!'), 'mdi-car-wash');
            return;
        }

        if ($timeTillWash < 10) {
            $this->createNotification('info', __('Washing required soon!'), 'mdi-car-wash');
        }
    }

    private function getTirePressureNotification(Vehicle $vehicle): void
    {
        $timeTill = $vehicle->tire_pressure_check_status['time'] ?? null;

        if (! isset($timeTill)) {
            return;
        }

        if ($timeTill < 10) {
            $this->createNotification('warning', __('Check tire pressure!'), 'mdi-car-tire-alert');
            return;
        }

        if ($timeTill < 20) {
            $this->createNotification('info', __('Check tire pressure soon!'), 'mdi-car-tire-alert');
        }
    }

    private function getLiquidsCheckNotification(Vehicle $vehicle): void
    {
        $timeTill = $vehicle->liquids_check_status['time'] ?? null;

        if (! isset($timeTill)) {
            return;
        }

        if ($timeTill < 5) {
            $this->createNotification('warning', __('Check liquids!'), 'mdi-oil');
            return;
        }

        if ($timeTill < 10) {
            $this->createNotification('info', __('Check liquids soon!'), 'mdi-oil');
        }
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
