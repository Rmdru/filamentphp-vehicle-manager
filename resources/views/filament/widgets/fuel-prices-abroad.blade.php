<x-filament::section>
    <div class="hidden md:block overflow-x-auto">
        @if (! empty($fuelPrices))
            <table class="fi-ta-table w-full text-sm">
                <thead>
                    <tr>
                        <th class="fi-ta-header-cell">
                            {{ __('Country') }}
                        </th>

                        @foreach ($fuelTypes as $fuelType)
                            <th class="fi-ta-header-cell">
                                {{ $fuelType }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($fuelPrices as $country => $fuels)
                        <tr class="fi-ta-row">
                            <td class="fi-ta-cell font-semibold flex gap-3 items-center">
                                <livewire:country-flag :country="$country" />
                                {{ config('countries')[$country]['name'] }}
                            </td>

                            @foreach ($fuelTypes as $key => $fuelType)
                                <td class="fi-ta-cell">
                                    @if(isset($fuels[$key]))
                                        <div
                                            title="{{ $country !== 'netherlands'
                                                ? __('Break-even full: :km km', ['km' => $fuels[$key]['max_detour_all_costs']])
                                                : '' }}"
                                        >
                                            <div>€ {{ $fuels[$key]['price'] }}/l</div>

                                            <div class="text-gray-500 text-xs">
                                                {{ $country !== 'netherlands'
                                                    ? $fuels[$key]['max_detour_only_fuel_costs'] . ' km'
                                                    : '' }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>


    <div class="md:hidden space-y-4">
        @forelse ($fuelPrices as $country => $fuels)
            <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">

                <div class="flex items-center gap-3 font-semibold mb-3">
                    <livewire:country-flag :country="$country" />
                    {{ config('countries')[$country]['name'] }}
                </div>

                <div class="space-y-2">
                    @foreach ($fuelTypes as $key => $fuelType)
                        <div class="flex justify-between text-sm">
                            {{ $fuelType }}

                            @if(isset($fuels[$key]))
                                <div class="text-right">
                                    <div>
                                        € {{ $fuels[$key]['price'] }}/l
                                    </div>

                                    @if($country !== 'netherlands')
                                        <div class="text-xs text-gray-500">
                                            {{ __('Break-even fuel: :km km', ['km' => $fuels[$key]['max_detour_only_fuel_costs']]) }}<br />
                                            {{ __('Break-even full: :km km', ['km' => $fuels[$key]['max_detour_all_costs']]) }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    @endforeach
                </div>

            </div>
        @empty
            <div class="p-4 text-center text-gray-500">
                {{ __('No fuel price data available.') }}
            </div>
        @endforelse
    </div>
</x-filament::section>
