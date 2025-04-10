<div class="mt-6">
    <x-filament::section :icon="$icon" collapsible>
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>
        <div class="flex flex-wrap gap-8 items-center">
            @if (! empty($icon) && empty($countryFlag) && empty($logo)&& empty($roadConfig))
                <div
                    class="p-2 rounded-full bg-white w-5/12 max-w-12 flex items-center text-black border border-gray-200 [&>svg]:max-h-8 [&>svg]:mx-auto">
                    @svg($icon)
                </div>
            @endif
            @if (! empty($logo))
                <div
                    class="p-2 rounded-full bg-white w-5/12 max-w-12 flex items-center text-black border border-gray-200 [&>svg]:max-h-8 [&>svg]:mx-auto">
                    <img src="{{ $logo }}" />
                </div>
            @endif
            @if (! empty($countryFlag))
                <livewire:country-flag :country="$countryFlag" />
            @endif
            @if (! empty($roadConfig))
                <livewire:road-badge :roadType="$roadConfig['roadType']" :road="$roadConfig['road']" :country="$roadConfig['country']" />
            @endif
            <div class="flex flex-wrap gap-4">
                @if (! empty($date))
                    <div class="flex gap-2 items-center">
                        <x-gmdi-calendar-month-r class="w-6 text-gray-400 dark:text-gray-500" />
                        {{ $date->isoFormat('MMM D, Y') }}
                    </div>
                @endif
                @if (! empty($price))
                    <div class="flex gap-2 items-center">
                        <x-mdi-hand-coin-outline class="w-6 text-gray-400 dark:text-gray-500" />
                        € {{ $price }}
                    </div>
                @endif
                @if (! empty($mileage))
                    <div class="flex gap-2 items-center">
                        <x-gmdi-route-r class="w-6 text-gray-400 dark:text-gray-500" />
                        {{ $mileage }} km
                    </div>
                @endif
                @if (! empty($location))
                    <div class="flex gap-2 items-center">
                        <x-gmdi-location-on-r class="w-6 text-gray-400 dark:text-gray-500" />
                        {{ $location }}
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-4">
                @if (! empty($badges))
                    @foreach($badges as $badge)
                        <x-filament::badge :color="$badge['color']" :icon="$badge['icon']">
                            {{ $badge['title'] }}
                        </x-filament::badge>
                    @endforeach
                @endif
            </div>
            @if (! empty($link))
                <x-filament::link :href="$link['url']" color="white" :icon="$link['icon']" class="last-of-type:ml-auto">
                    {{ $link['text'] }}
                </x-filament::link>
            @endif
        </div>
    </x-filament::section>
</div>