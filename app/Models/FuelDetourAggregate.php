<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelDetourAggregate extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'vehicle_id',
        'fuel_price_id',
        'max_detour_only_fuel_costs',
        'max_detour_all_costs',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fuelPrice(): BelongsTo
    {
        return $this->belongsTo(FuelPrice::class);
    }
}
