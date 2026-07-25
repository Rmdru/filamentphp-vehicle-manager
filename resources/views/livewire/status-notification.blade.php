<div>
    @foreach($notifications as $notification)
        <div class="p-2 mb-4 border rounded-lg {{ $notification['textColor'] }} {{ $notification['borderColor'] }}">
            <div class="flex items-center gap-1 justify-between">
                <div class="flex gap-1">
                    <div class="flex gap-1">
                        @if (! empty($notification['typeIcon']))
                            @svg($notification['typeIcon'], ['class' => 'w-6 h-6'])
                        @endif
                        @if (! empty($notification['icon']))
                            @svg($notification['icon'], ['class' => 'w-6 h-6'])
                        @endif
                    </div>
                    @if (! empty($notification['text']))
                        &nbsp;<h3 class="font-medium">
                            {{ $notification['text'] }}
                            @if ($notification['hasModal'])
                                @php
                                    $modalTitle = $notification['modalTitle'] ?? __('Detailed Information');
                                    $modalHeading = $notification['modalHeading'] ?? __('Detailed Information');
                                    $modalData = $notification['data'] ?? [];
                                @endphp

                                <x-filament::modal
                                    width="7xl"
                                    :title="$modalTitle"
                                    :id="'modal-'.$notification['key']"
                                >
                                    <x-slot name="trigger">
                                        <x-filament::link size="medium" color="{{ $notification['textColor'] }}" class="underline align-end">
                                            {{ __('More information') }}
                                        </x-filament::link>
                                    </x-slot>
                                    <div class="text-black dark:text-white border-none">
                                        <x-slot name="heading">
                                            {{ $modalHeading }}
                                        </x-slot>

                                        @forelse ($modalData as $item)
                                            @if (is_array($item) && array_is_list($item))
                                                <p class="font-bold text-lg mt-4 mb-2">{{ __('Item :key', ['key' => $loop->iteration]) }}</p>
                                            @endif

                                            <div class="relative overflow-x-auto shadow-xs rounded border border-default">
                                                <table class="w-full text-sm text-left rtl:text-right">
                                                    @foreach ($item as $key => $value)
                                                        <tr class="border-b border-default">
                                                            <th scope="row" class="px-3 py-2 font-bold text-heading whitespace-nowrap">{{ __((string) str($key)->replace('_', ' ')->ucfirst()) }}:</th>
                                                            <td class="px-3 py-2 font-light dark:text-gray-400">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        @empty
                                            <p class="font-light">{{ __('No details available') }}</p>
                                        @endforelse
                                    </div>
                                </x-filament::modal>
                            @endif
                        </h3>
                    @endif
                </div>
                @if (! empty($notification['linkText']) && ! empty($notification['linkUrl']))
                    <a class="align-end flex gap-1 underline" href="{{ $notification['linkUrl'] }}">
                        {{ $notification['linkText'] }}
                        @svg('gmdi-check-r', ['class' => 'w-6 h-6'])
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>
