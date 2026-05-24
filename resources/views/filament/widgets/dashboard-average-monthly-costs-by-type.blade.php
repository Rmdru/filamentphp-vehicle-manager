<div class="overflow-x-auto">
    <table class="fi-ta-table w-full text-sm">
        <thead>
            <tr>
                <th class="fi-ta-header-cell text-left p-2">
                    {{ __('Type') }}
                </th>
                <th class="fi-ta-header-cell text-right p-2">
                    {{ __('Average per month') }}
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach ($costs as $cost)
                <tr class="fi-ta-row border-t border-gray-500">
                    <td class="fi-ta-cell p-2">
                        <div class="flex items-center gap-3">
                            @svg($cost['icon'], ['class' => 'w-5 h-5 text-gray-500'])
                            <span>{{ $cost['label'] }}</span>
                        </div>
                    </td>
                    <td class="fi-ta-cell text-right p-2">
                        € {{ $cost['amount'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
