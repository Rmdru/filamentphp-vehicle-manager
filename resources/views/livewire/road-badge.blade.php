<div class="flex gap-1">
    @foreach($badges as $badge)
        <div class="rounded w-fit px-1 font-bold {{ $badge['roadTypeConfig']['background_color'] }} {{ $badge['roadTypeConfig']['color'] }}">
            {{ $badge['roadNumber'] }}
        </div>
    @endforeach
</div>
