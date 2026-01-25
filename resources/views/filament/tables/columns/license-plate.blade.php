@livewire('license-plate', [
    'vehicleId' => $getRecord()->id,
], key('license-plate-' . $getRecord()->id))