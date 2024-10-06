<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Insurance extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'insurance_company',
        'start_date',
        'end_date',
        'type',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
