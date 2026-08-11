<x-filament::page>
    @if ($vehicle->image_exists)
        <img src="{{ $vehicle->image_url }}" class="w-96" />
    @endif
    <div class="w-full flex gap-4 items-center flex-wrap">
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
        <button id="dashboard-filter-toggle" type="button" aria-label="Toggle date filter" class="sm:ml-auto inline-flex items-center px-2 py-1 rounded-md text-sm border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
        </button>
    </div>
    <form method="get" id="dashboard-date-filter" class="hidden flex flex-wrap items-center gap-3 my-4">
        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('Date range:') }}</label>
        <input type="date" name="dashboard_start" value="{{ request()->query('dashboard_start', '') }}" class="rounded-md border px-3 py-1 text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white" />
        <span class="text-sm text-gray-400">—</span>
        <input type="date" name="dashboard_end" value="{{ request()->query('dashboard_end', '') }}" class="rounded-md border px-3 py-1 text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white" />
        <x-filament::button type="submit" class="ml-2">{{ __('Apply') }}</x-filament::button>
        <a href="{{ url()->current() }}" class="ml-2 text-sm text-gray-500 dark:text-gray-400 hover:underline">{{ __('Reset') }}</a>
        <div class="ml-4 flex gap-2">
            <button type="button" class="inline-flex items-center px-2 py-1 rounded-md text-sm border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5" onclick="setPreset('month')">{{ __('Month') }}</button>
            <button type="button" class="inline-flex items-center px-2 py-1 rounded-md text-sm border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5" onclick="setPreset('month_ago')">{{ __('Month ago') }}</button>
            <button type="button" class="inline-flex items-center px-2 py-1 rounded-md text-sm border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5" onclick="setPreset('year_ago')">{{ __('Year ago') }}</button>
            <button type="button" class="inline-flex items-center px-2 py-1 rounded-md text-sm border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-transparent hover:bg-gray-50 dark:hover:bg-white/5" onclick="setPreset('year')">{{ __('Year') }}</button>
        </div>
    </form>

    <script>
        document.getElementById('dashboard-filter-toggle').addEventListener('click', function() {
            const form = document.getElementById('dashboard-date-filter');
            form.classList.toggle('hidden');
            if (! form.classList.contains('hidden')) {
                const start = form.querySelector('input[name="dashboard_start"]');
                if (start) start.focus();
            }
        });

        function setPreset(preset) {
            const startInput = document.querySelector('input[name="dashboard_start"]');
            const endInput = document.querySelector('input[name="dashboard_end"]');
            const now = new Date();

            if (preset === 'day') {
                const today = now.toISOString().slice(0,10);
                startInput.value = today;
                endInput.value = today;
            }

            if (preset === 'month') {
                const y = now.getFullYear();
                const m = (now.getMonth()+1).toString().padStart(2,'0');
                startInput.value = `${y}-${m}-01`;
                const lastDay = new Date(y, now.getMonth()+1, 0).getDate();
                endInput.value = `${y}-${m}-${String(lastDay).padStart(2,'0')}`;
            }

            if (preset === 'month_ago') {
                // from same day last month until today
                const startDate = new Date(now);
                startDate.setMonth(startDate.getMonth() - 1);
                const sy = startDate.getFullYear();
                const sm = String(startDate.getMonth() + 1).padStart(2,'0');
                const sd = String(startDate.getDate()).padStart(2,'0');
                startInput.value = `${sy}-${sm}-${sd}`;
                endInput.value = now.toISOString().slice(0,10);
            }

            if (preset === 'year') {
                const y = now.getFullYear();
                startInput.value = `${y}-01-01`;
                endInput.value = `${y}-12-31`;
            }

            if (preset === 'year_ago') {
                // from same day last year until today
                const startDate = new Date(now);
                startDate.setFullYear(startDate.getFullYear() - 1);
                const sy = startDate.getFullYear();
                const sm = String(startDate.getMonth() + 1).padStart(2,'0');
                const sd = String(startDate.getDate()).padStart(2,'0');
                startInput.value = `${sy}-${sm}-${sd}`;
                endInput.value = now.toISOString().slice(0,10);
            }

            document.getElementById('dashboard-date-filter').submit();
        }
    </script>
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
    @if (! in_array($vehicle->powertrain, ['electricity', 'hydrogen']))
        <x-filament::section
            icon="gmdi-local-gas-station-r"
            collapsible
            persist-collapsed
            id="statistics"
        >
            <x-slot name="heading">
                <span class="flex gap-2">
                    {{ __('Fuel prices abroad') }}
                </span>
            </x-slot>
            @livewire(\App\Filament\Widgets\DashboardFuelPricesAbroad::class)
        </x-filament::section>
        <x-filament::section
            icon="gmdi-local-gas-station-r"
            collapsible
            persist-collapsed
            id="fuel-usage-by-type"
        >
            <x-slot name="heading">
                <span class="flex gap-2">
                    {{ __('Fuel usage by fuel type') }}
                </span>
            </x-slot>
            @livewire(\App\Filament\Widgets\DashboardFuelUsageByType::class)
        </x-filament::section>
    @endif
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
        id="average-monthly-costs"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Average monthly costs') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardAverageMonthlyCostsByType::class)
    </x-filament::section>
    <x-filament::section
        icon="gmdi-bar-chart-r"
        collapsible
        persist-collapsed
        id="monthly-costs"
    >
        <x-slot name="heading">
            <span class="flex gap-2">
                {{ __('Monthly costs') }}
            </span>
        </x-slot>
        @livewire(\App\Filament\Widgets\DashboardCostsChart::class)
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
