<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parking extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'start_time',
        'end_time',
        'type',
        'location',
        'payment_method',
        'price',
        'company',
    ];

    protected $casts = [
        'start_time' => 'date:Y-m-d H:i:s',
        'end_time' => 'date:Y-m-d H:i:s',
    ];

    protected $table = 'parking';


    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
