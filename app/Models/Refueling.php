<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refueling extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'date',
        'gas_station',
        'fuel_type',
        'amount',
        'unit_price',
        'total_price',
        'mileage_begin',
        'mileage_end',
        'fuel_consumption_onboard_computer',
        'fuel_consumption',
        'costs_per_kilometer',
        'tires',
        'climate_control',
        'routes',
        'driving_style',
        'avg_speed',
        'discount',
        'payment_method',
        'comments',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'climate_control' => 'array',
        'routes' => 'array',
    ];

    /**
     * Get the vehicle of this refueling.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
