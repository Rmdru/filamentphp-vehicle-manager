<x-filament::page>
    <div class="w-fit flex gap-2 items-center">
        {{ __('Vehicle:') }}
        <x-filament::badge color="gray" icon="si-{{$vehicle->brand}}" size="xl">
            {{ $vehicle->brand . ' ' . $vehicle->model }}
        </x-filament::badge>
        <livewire:license-plate :vehicleId="$vehicle->id" />
    </div>
    <x-filament::section icon="gmdi-notifications-r" collapsible="">
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Notifications') }}
            </span>
        </x-slot>
        <livewire:status-notification :vehicleId="$vehicle->id" />
    </x-filament::section>
    <x-filament::section icon="mdi-list-status" collapsible="">
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Status') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardStatusOverview::class)
    </x-filament::section>
    <x-filament::section icon="gmdi-show-chart-r" collapsible="">
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Statistics') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardStatsOverview::class)
    </x-filament::section>
</x-filament::page>
