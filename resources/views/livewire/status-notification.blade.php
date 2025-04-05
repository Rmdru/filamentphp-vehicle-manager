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
                        &nbsp;<h3 class="font-medium">{{ $notification['text'] }}</h3>
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
