<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Component;

class StatusBadge extends Component
{
    public string $vehicleId;

    public function mount($vehicleId): void
    {
        $this->vehicleId = $vehicleId;
    }

    public function render(): View
    {
        $badge = Vehicle::getStatusBadge($this->vehicleId);

        return view('livewire.status-badge', [
            'badge' => $badge,
        ]);
    }
}
