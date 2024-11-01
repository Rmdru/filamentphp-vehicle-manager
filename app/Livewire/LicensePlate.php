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
        $countries = config('countries');
        $vehicle = Vehicle::selected()->first();

        if ($this->vehicleId) {
            $vehicle = Vehicle::where('id', $this->vehicleId)->latest()->first();
        }

        $licensePlateConfig = $countries[$vehicle->country_registration]['license_plate'];

        if (empty($licensePlateConfig)) {
            $licensePlateConfig = [
                'border' => null,
                'color' => 'text-black',
                'filament_color' => 'gray',
                'background_color' => 'bg-white',
                'prefix' => null,
                'eu_bar' => false,
            ];
        }

        return view('livewire.license-plate', [
            'licensePlate' => $vehicle->license_plate,
            'licensePlateConfig' => $licensePlateConfig,
        ]);
    }
}
