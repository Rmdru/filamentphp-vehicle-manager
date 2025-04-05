<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Tax extends Model
{
    use HasUuids;
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'start_date',
        'end_date',
        'price',
        'invoice_day',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function getMonthsAttribute(): Collection
    {
        if (! $this->start_date || ! $this->end_date) {
            return collect();
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        if ($this->start_date <= today() && $this->end_date > today()) {
            $end = today();
        }

        return $this->getMonthsBetweenDates($start, $end);
    }

    public function getMonthsBetweenDates(Carbon $startDate, Carbon $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        $months = new Collection();

        while ($start <= $end) {
            $months->push($start->format('Y-m'));
            $start->addMonth();
        }

        return $months;
    }

    public function getNextInvoiceDate($startDate, $endDate, $invoiceDay)
    {
        $today = Carbon::today();

        if (Carbon::parse($startDate)->greaterThan($today) || Carbon::parse($endDate)->lessThan($today)) {
            return null;
        }

        $nextInvoiceDate = Carbon::createFromDate($today->year, $today->month, $invoiceDay);

        if ($nextInvoiceDate->lessThan($today)) {
            $nextInvoiceDate->addMonth();
        }

        if ($nextInvoiceDate->greaterThan(Carbon::parse($endDate))) {
            return null;
        }

        return $nextInvoiceDate;
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rules(): array
    {
        return [
            'invoice_day' => 'required|integer|min:1|max:31',
        ];
    }
}
