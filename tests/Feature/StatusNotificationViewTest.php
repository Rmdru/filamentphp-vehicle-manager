<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

test('maintenance notifications can show a modal with the deadline date', function () {
    $notifications = [[
        'text' => __('Maintenance required soon'),
        'key' => 'maintenance',
        'hasModal' => true,
        'modalTitle' => __('Maintenance deadline'),
        'modalHeading' => __('Maintenance deadline'),
        'data' => [[
            'deadline_date' => '2026-08-12',
            'time_diff_humans' => 'in 2 months',
        ]],
        'textColor' => 'text-orange-400',
        'borderColor' => 'border-orange-500',
        'typeIcon' => 'gmdi-error-r',
        'icon' => 'mdi-car-wrench',
    ]];

    $html = view('livewire.status-notification', ['notifications' => $notifications])->render();

    expect($html)->toContain(__('More information'))
        ->and($html)->toContain(__('Maintenance deadline'))
        ->and($html)->toContain('2026-08-12');
});
