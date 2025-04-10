<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accident extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $fillable = [
        'vehicle_id',
        'datetime',
        'location',
        'type',
        'description',
        'guilty',
        'situation',
        'damage_own',
        'damage_own_insured',
        'damage_others',
        'damage_others_insured',
        'total_price',
        'attributes',
    ];

    protected $casts = [
        'datetime' => 'datetime:Y-m-d H:i:s',
        'situation' => 'json',
        'attributes' => 'json',
    ];
    
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
