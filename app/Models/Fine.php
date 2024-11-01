<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'type',
        'fact',
        'description',
        'icon',
        'country',
        'road',
        'road_type',
        'road_distance_marker',
        'location',
        'provider',
        'date',
        'price',
        'fine',
        'payed',
        'payment_date',
        'payment_method',
        'sanctions',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'fine' => 'boolean',
        'payed' => 'boolean',
        'sanctions' => 'json',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
