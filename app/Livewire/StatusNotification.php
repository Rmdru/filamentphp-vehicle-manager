<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Vehicle;
use App\Services\VehicleStatusService;
use Filament\Facades\Filament;
use Illuminate\View\View;
use Livewire\Component;

class StatusNotification extends Component
{
    private array $notifications = [];

    public function mount(VehicleStatusService $statusService): void
    {
        $vehicle = Filament::getTenant();
        $this->notifications = $statusService->getNotifications($vehicle);
    }

    public function render(): View
    {
        return view('livewire.status-notification', [
            'notifications' => $this->notifications,
        ]);
    }
}