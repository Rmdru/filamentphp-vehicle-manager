@if ($licensePlateConfig['eu_bar'])
    <div class="{{ ! empty($licensePlateConfig['border']) ? $licensePlateConfig['border'] . ' border' : '' }} rounded w-fit font-bold flex items-center gap-0 overflow-hidden">
        <div class="bg-blue-800 text-white px-1">{{ $licensePlateConfig['prefix'] }}</div>
        <div class="{{ $licensePlateConfig['background_color'] }} {{ $licensePlateConfig['color'] }} px-1">{{ $licensePlate }}</div>
    </div>
@else
    <div class="{{ ! empty($licensePlateConfig['border']) ? $licensePlateConfig['border'] . ' border' : '' }} rounded w-fit font-bold flex items-center gap-0 overflow-hidden">
        <div class="{{ $licensePlateConfig['background_color'] }} {{ $licensePlateConfig['color'] }} px-1">{{ $licensePlate }}</div>
    </div>
@endif
