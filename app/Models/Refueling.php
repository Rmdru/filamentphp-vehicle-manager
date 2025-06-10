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

    protected $fillable = [
        'vehicle_id',
        'date',
        'country',
        'gas_station',
        'fuel_type',
        'amount',
        'unit_price',
        'total_price',
        'mileage_begin',
        'mileage_end',
        'percentage',
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
        'service_by_attendant',
        'charge_time',
        'comments',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'climate_control' => 'array',
        'routes' => 'array',
        'servie_by_attendant' => 'boolean',
        'charge_time' => 'datetime:H:i',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
