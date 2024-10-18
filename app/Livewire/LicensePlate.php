<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Component;

class LicensePlate extends Component
{
    public $vehicleId;

    public function mount($vehicleId): void
    {
        $this->vehicleId = $vehicleId;
    }

    public function render(): View
    {
        $licensePlate = Vehicle::selected()->first()->license_plate;

        if ($this->vehicleId) {
            $licensePlate = Vehicle::where('id', $this->vehicleId)->latest()->first()->license_plate;
        }

        return view('livewire.license-plate', [
            'licensePlate' => $licensePlate,
        ]);
    }
}
