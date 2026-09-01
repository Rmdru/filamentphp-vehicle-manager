<?php

use App\Models\Ferry;
use Carbon\Carbon;

uses(Tests\TestCase::class);

describe('Ferry casts', function () {
    it('casts start_date and end_date to datetime Carbon instances', function () {
        $ferry = new Ferry([
            'start_date' => '2026-06-15 08:30:00',
            'end_date' => '2026-06-15 14:45:00',
        ]);

        expect($ferry->start_date)->toBeInstanceOf(Carbon::class)
            ->and($ferry->end_date)->toBeInstanceOf(Carbon::class)
            ->and($ferry->start_date->format('Y-m-d H:i:s'))->toBe('2026-06-15 08:30:00')
            ->and($ferry->end_date->format('Y-m-d H:i:s'))->toBe('2026-06-15 14:45:00');
    });
});
