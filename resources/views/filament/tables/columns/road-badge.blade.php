@php
    $record = $getRecord();
@endphp

@livewire('road-badge', [
    'roadType' => $record->road_type,
    'road' => $record->road,
    'country' => $record->country,
], key('road-badge-' . $record->id))