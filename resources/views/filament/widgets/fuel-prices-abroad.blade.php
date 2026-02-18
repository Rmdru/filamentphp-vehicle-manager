<x-filament::section>
    <div class="fi-ta overflow-x-auto">
        <table class="fi-ta-table w-full text-sm">
            <thead>
                <tr>
                    <th class="fi-ta-header-cell">{{ __('Country') }}</th>
                    @foreach ($fuelTypes as $fuelType)
                        <th class="fi-ta-header-cell">{{ $fuelType }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($fuelPrices as $country => $fuels)
                    <tr class="fi-ta-row">
                        <td class="fi-ta-cell font-semibold flex gap-3 items-center">
                            <livewire:country-flag :country="$country"  />
                            {{ config('countries')[$country]['name'] }}
                        </td>

                        @foreach ($fuelTypes as $key => $fuelType)
                            <td class="fi-ta-cell">
                                @if(isset($fuels[$key]))
                                    <div title="{{ $country !== 'netherlands' ? __('Number of kilometers that can be driven with all vehicle costs: :max_detour_all_costs km', ['max_detour_all_costs' => $fuels[$key]['max_detour_all_costs']]) : '' }}">
                                        <div>€ {{ $fuels[$key]['price'] }}/l</div>
                                        <div class="text-gray-500 text-xs">
                                            {{ $country !== 'netherlands' ? __(':max_detour_only_fuel_costs km', ['max_detour_only_fuel_costs' => $fuels[$key]['max_detour_only_fuel_costs']]) : '' }}
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
    </div>
</x-filament::section>
