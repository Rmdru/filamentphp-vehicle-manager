<x-filament::page>
    @if ($predictions->count())
        <x-filament::section icon="gmdi-auto-awesome-r" class="my-3" collapsible collapsed>
            <x-slot name="heading">
                {{ __('Predictions') }}
            </x-slot>
            @foreach($predictions as $month => $itemsInMonth)
                <h1 class="font-bold text-xl mt-3">{{ str($month)->ucfirst() }}</h1>
                @foreach ($itemsInMonth as $item)
                    <livewire:timeline-item
                        :icon="$item->icon ?? ''"
                        :logo="''"
                        :countryFlag="$item->countryFlag ?? ''"
                        :heading="$item->heading"
                        :date="$item->date"
                        :price="$item->price ?? null"
                        :mileage="(! empty($item->mileage_begin) && ! empty($item->mileage_end)) ? $item->mileage_end - $item->mileage_begin : null"
                        :location="$item->location ?? $item->garage ?? $item->gas_station ?? null"
                        :badges="$item->badges ?? []"
                        :roadConfig="$item->roadConfig ?? []"
                        :link="[]"
                    />
                @endforeach
            @endforeach
        </x-filament::section>
    @endif
    @foreach ($historyItems as $month => $itemsInMonth)
        <x-filament::fieldset>
            <h1 class="font-bold text-xl">{{ str($month)->ucfirst() }}</h1>
            @foreach ($itemsInMonth as $item)
                <livewire:timeline-item
                    :icon="$item->icon ?? ''"
                    :logo="$item->logo ?? ''"
                    :countryFlag="$item->countryFlag ?? ''"
                    :heading="$item->heading ?? ''"
                    :date="$item->date"
                    :price="$item->price ?? null"
                    :mileage="(! empty($item->mileage_begin) && ! empty($item->mileage_end)) ? $item->mileage_end - $item->mileage_begin : null"
                    :location="$item->location ?? $item->garage ?? $item->gas_station ?? null"
                    :badges="$item->badges ?? []"
                    :roadConfig="$item->roadConfig ?? []"
                    :link="[
                        'url' => route('filament.account.resources.' . $item->link . '.edit', [
                            'tenant' => $tenant->id,
                            'record' => $item->id,
                        ]),
                        'icon' => 'gmdi-remove-red-eye-r',
                        'text' => __('Show'),
                    ]"
                />
            @endforeach
        </x-filament::fieldset>
    @endforeach
    @if (! $historyItems->count())
        <x-filament::fieldset>
            <h1 class="font-bold text-xl">{{ __('Nothing to show') }}</h1>
        </x-filament::fieldset>
    @endif
</x-filament::page>