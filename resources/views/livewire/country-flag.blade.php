<div class="flex gap-2 items-center">
    <img src="https://flagsapi.com/{{ $iso_code }}/flat/64.png" alt="{{ $iso_code }}" class="h-10" />
    @if ($showName)
    {{ $name }}
    @endif
</div>
