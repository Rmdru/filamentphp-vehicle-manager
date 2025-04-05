<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use App\Services\VehicleStatusService;
use Illuminate\View\View;
use Livewire\Component;

class StatusNotification extends Component
{
    private array $notifications = [];

    public function mount(VehicleStatusService $statusService): void
    {
        $vehicle = Vehicle::selected()->first();
        $this->notifications = $statusService->getNotifications($vehicle);
    }

    public function render(): View
    {
        return view('livewire.status-notification', [
            'notifications' => $this->notifications,
        ]);
    }
}