<x-filament::page>
    @if (! empty($vehicle->image_url))
        <img src="{{ $vehicle->image_url }}" class="w-96" />
    @endif
    <div class="w-fit flex gap-4 items-center">
        <div class="flex gap-2 items-center">
            @svg('si-' . str($vehicle->brand)->replace([' ', '-'], '')->lower()->ascii(), ['class' => 'w-8 h-8'])
            {{ $vehicle->brand . ' ' . $vehicle->model }}
        </div>
        <livewire:license-plate :vehicleId="$vehicle->id" />
        @if (! empty($vehicle->mileage_latest))
            <x-filament::badge>
                {{ $vehicle->mileage_latest }} km
            </x-filament::badge>
        @endif
    </div>
    <x-filament::section
        icon="gmdi-notifications-active-r"
        collapsible
        persist-collapsed
        id="notifications"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Notifications') }}
            </span>
        </x-slot>
        <livewire:status-notification />
    </x-filament::section>
    <x-filament::section
        icon="mdi-list-status"
        collapsible
        persist-collapsed
        id="status"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Status') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardStatusOverview::class)
    </x-filament::section>
    <x-filament::section
        icon="gmdi-show-chart-r"
        collapsible
        persist-collapsed
        id="statistics"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Statistics') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardStatsOverview::class)
    </x-filament::section>
    <x-filament::section
        icon="gmdi-bar-chart-r"
        collapsible
        persist-collapsed
        id="montly-costs"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Monthly costs') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardCostsChart::class)
    </x-filament::section>
    <x-filament::section
        icon="gmdi-local-gas-station-r"
        collapsible
        persist-collapsed
        id="cheapest-gas-stations"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Cheapest gas stations') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardCheapestGasStations::class)
    </x-filament::section>
    <x-filament::section
        icon="mdi-hand-coin-outline"
        collapsible
        persist-collapsed
        id="most-recent-costs"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Most recent costs') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardLatestCosts::class)
    </x-filament::section>
</x-filament::page>
