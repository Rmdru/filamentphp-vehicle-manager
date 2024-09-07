<x-filament::page>
    <x-filament::fieldset>
        <x-slot name="label">
            {{ __('Notifications') }}
        </x-slot>
        <livewire:status-notification :vehicleId="$record->id" />
    </x-filament::fieldset>
    <x-filament::fieldset>
        <x-slot name="label">
            {{ __('Status') }}
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardStatusOverview::class)
    </x-filament::fieldset>
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @else
        {{ $this->form }}
    @endif
</x-filament::page>
