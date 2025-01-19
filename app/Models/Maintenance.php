<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
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
        'garage',
        'type_maintenance',
        'apk',
        'apk_date',
        'airco_check',
        'description',
        'tasks',
        'total_price',
        'mileage',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'apk' => 'boolean',
        'apk_date' => 'date:Y-m-d',
        'tasks' => 'array',
    ];

    /**
     * Get the vehicle of this maintenance.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
