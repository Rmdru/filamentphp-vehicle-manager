<div class="flex gap-1">
    @foreach($badges as $badge)
        <div class="rounded w-fit px-1 font-bold {{ $badge['roadTypeConfig']['backgroundColor'] }} {{ $badge['roadTypeConfig']['color'] }}">
            {{ $badge['roadNumber'] }}
        </div>
    @endforeach
</div>
