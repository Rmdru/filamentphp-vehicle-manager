<?php

use App\Models\Insurance;
use App\Models\Tax;
use Carbon\Carbon;

describe('Insurance month selection', function () {
    it('prefers the newer insurance policy when two policies overlap in the same month', function () {
        $olderInsurance = new Insurance();
        $olderInsurance->id = 'older-insurance';
        $olderInsurance->start_date = '2025-01-01';
        $olderInsurance->end_date = '2026-07-11';
        $olderInsurance->invoice_day = 11;

        $newerInsurance = new Insurance();
        $newerInsurance->id = 'newer-insurance';
        $newerInsurance->start_date = '2026-07-11';
        $newerInsurance->end_date = null;
        $newerInsurance->invoice_day = 11;

        $activeInsurance = Insurance::getActiveMonthlyRecordForMonth(
            collect([$olderInsurance, $newerInsurance]),
            Carbon::create(2026, 7, 1)
        );

        expect($activeInsurance)->not->toBeNull()
            ->and($activeInsurance->id)->toBe('newer-insurance');
    });

    it('prefers the newer tax policy when two tax periods overlap in the same month', function () {
        $olderTax = new Tax();
        $olderTax->id = 'older-tax';
        $olderTax->start_date = '2025-01-01';
        $olderTax->end_date = '2026-07-11';
        $olderTax->invoice_day = 11;

        $newerTax = new Tax();
        $newerTax->id = 'newer-tax';
        $newerTax->start_date = '2026-07-11';
        $newerTax->end_date = null;
        $newerTax->invoice_day = 11;

        $activeTax = Tax::getActiveMonthlyRecordForMonth(
            collect([$olderTax, $newerTax]),
            Carbon::create(2026, 7, 1)
        );

        expect($activeTax)->not->toBeNull()
            ->and($activeTax->id)->toBe('newer-tax');
    });
});
