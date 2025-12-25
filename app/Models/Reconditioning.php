<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reconditioning extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'date',
        'price',
        'location',
        'type',
        'executor',
        'description',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function latestWash(): ?Reconditioning
    {
        return Reconditioning::where('vehicle_id', Filament::getTenant()->id)
            ->where(function ($query) {
                $query->where('type', 'LIKE', '%carwash%')
                    ->orWhere('type', 'LIKE', '%exterior_cleaning%');
            })
            ->orderByDesc('date')
            ->first();
    }
}
