<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'date',
        'country',
        'fuel_type',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function detourAggregate()
    {
        return $this->hasOne(FuelDetourAggregate::class);
    }
}
