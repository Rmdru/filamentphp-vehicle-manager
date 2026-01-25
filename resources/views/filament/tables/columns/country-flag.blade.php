@livewire('country-flag', [
    'country' => $getRecord()->country ?? $getRecord()->country_registration,
    'showName' => $showName ?? false,
], key('country-' . $getRecord()->id))