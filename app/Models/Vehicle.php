<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Vehicle extends Model
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
        'user_id',
        'brand',
        'model',
        'version',
        'engine',
        'factory_specification_fuel_consumption',
        'mileage_start',
        'mileage_latest',
        'purchase_date',
        'purchase_price',
        'license_plate',
        'powertrain',
        'country_registration',
        'is_private',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date:Y-m-d',
        'private' => 'boolean',
    ];

    protected $appends = [
        'fuel_status',
        'maintenance_status',
        'apk_status',
        'airco_check_status',
        'insurance_status',
        'tax_status',
    ];

    protected static function booted()
    {
        static::addGlobalScope('ownVehicles', function (Builder $builder) {
            $builder->where('user_id', Auth::id());
        });
    }

    public function scopeSelected(Builder $query): void
    {
        $vehicleId = Session::get('Dashboard_filters', '')['vehicleId'] ?? Vehicle::latest()->first()->id;

        Session::put('vehicle_id', $vehicleId);

        $query->where([
            'id' => Session::get('vehicle_id'),
            'user_id' => Auth::id(),
        ]);
    }

    public function scopeOnlyDrivable(Builder $query): void
    {
        $query->where([
            'status' => 'drivable',
        ]);
    }

    public function getFullNameAttribute(): string
    {
        $brands = config('vehicles.brands');

        return $brands[$this->brand] . ' ' . $this->model;
    }

    public function getFuelStatusAttribute(): ?int
    {
        if ($this->refuelings->isNotEmpty() && $this->refuelings->count() > 0) {
            $latestRefueling = $this->refuelings->sortByDesc('date')->first();

            if (! empty($latestRefueling) && $latestRefueling->fuel_type = 'Premium Unleaded') {
                $diff = Carbon::parse($latestRefueling->date)->addMonths(2)->diffInDays(now());
                return (int) max(0, $diff - ($diff * 2));
            }
        }

        return null;
    }

    public function getMaintenanceStatusAttribute(): array
    {
        $maintenanceTypes = ['small_maintenance', 'maintenance', 'big_maintenance'];

        if ($this->maintenances->isNotEmpty()) {
            $latestMaintenance = $this->maintenances->whereIn('type_maintenance', $maintenanceTypes)->sortByDesc('date')->first();

            $maintenanceDate = Carbon::parse($latestMaintenance->date ?? now())->addYear();
            $maintenanceDiff = $maintenanceDate->diffInDays(now());
            $timeDiffHumans = $maintenanceDate->diffForHumans();

            $timeTillMaintenance = max(0, $maintenanceDiff - ($maintenanceDiff * 2));

            $distanceTillMaintenance = 15000 + $latestMaintenance->mileage_begin - $this->mileage_latest;
        }

        if ($this->maintenances->isEmpty()) {
            $maintenanceDate = now()->addYear();
            $maintenanceDiff = $maintenanceDate->diffInDays(now());
            $timeTillMaintenance = max(0, $maintenanceDiff - ($maintenanceDiff * 2));
            $timeDiffHumans = $maintenanceDate->diffForHumans();
            $distanceTillMaintenance = 15000;
        }

        return [
            'time' => $timeTillMaintenance,
            'timeDiffHumans' => $timeDiffHumans,
            'distance' => $distanceTillMaintenance,
        ];
    }

    public function getApkStatusAttribute(): array
    {
        if ($this->maintenances->isNotEmpty()) {
            $latestApk = $this->maintenances->where('apk', true)->sortByDesc('date')->first();

            $apkDate = Carbon::parse($latestApk->date ?? now())->addYear();
            $apkDiff = $apkDate->diffInDays(now());
            $timeDiffHumans = $apkDate->diffForHumans();

            $timeTillApk = max(0, $apkDiff - ($apkDiff * 2));
        }

        if ($this->maintenances->isEmpty()) {
            $apkDiff = now()->addYear()->diffInDays(now());
            $timeTillApk = max(0, $apkDiff - ($apkDiff * 2));
            $timeDiffHumans = now()->addYear()->diffForHumans();
        }

        return [
            'time' => $timeTillApk,
            'timeDiffHumans' => $timeDiffHumans,
        ];
    }

    public function getAircoCheckStatusAttribute(): array
    {
        if ($this->maintenances->isNotEmpty()) {
            $latestAircoCheck = $this->maintenances->where('airco_check', true)->sortByDesc('date')->first();

            $aircoCheckDate = Carbon::parse($latestAircoCheck->date)->addYears(2);
            $timeTillAircoCheckDiff = $aircoCheckDate->diffInDays(now());
            $timeTillAircoCheck = max(0, $timeTillAircoCheckDiff - ($timeTillAircoCheckDiff * 2));
            $timeDiffHumans = $aircoCheckDate->diffForHumans();

            return [
                'time' => $timeTillAircoCheck,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getInsuranceStatusAttribute(): array
    {
        if ($this->insurances->isNotEmpty()) {
            $insurance = $this->insurances->where('start_date', '<=', today())->sortByDesc('start_date')->first();

            $timeTillInsuranceDiff = $insurance->end_date->diffInDays(now());
            $timeTillInsuranceEndDate = max(0, $timeTillInsuranceDiff - ($timeTillInsuranceDiff * 2));
            $timeDiffHumans = $insurance->end_date->diffForHumans();

            return [
                'time' => $timeTillInsuranceEndDate,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getTaxStatusAttribute(): array
    {
        if ($this->taxes->isNotEmpty()) {
            $tax = $this->taxes->where('start_date', '<=', today())->sortByDesc('start_date')->first();

            $timeTillTaxDiff = $tax->end_date->diffInDays(now());
            $timeTillTaxEndDate = max(0, $timeTillTaxDiff - ($timeTillTaxDiff * 2));
            $timeDiffHumans = $tax->end_date->diffForHumans();

            return [
                'time' => $timeTillTaxEndDate,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getStatusBadge(string $vehicleId = '', string $item = '')
    {
        $selectedVehicle = Vehicle::selected()->first();

        if ($vehicleId) {
            $selectedVehicle = Vehicle::where('id', $vehicleId)->latest()->first();
        }

        $timeTillRefueling = $selectedVehicle->fuel_status ?? null;
        $maintenanceStatus = $selectedVehicle->maintenance_status ?? null;
        $timeTillApk = $selectedVehicle->apk_status['time'] ?? null;
        $timeTillAircoCheck = $selectedVehicle->airco_check_status['time'] ?? null;
        $timeTillInsuranceEndDate = $selectedVehicle->insurance_status['time'] ?? null;
        $timeTillTaxEndDate = $selectedVehicle->tax_status['time'] ?? null;

        $priorities = [
            'success' => [
                'color' => 'success',
                'icon' => 'gmdi-check-r',
                'text' => __('OK'),
            ],
            'info' => [
                'color' => 'info',
                'icon' => 'gmdi-info-r',
                'text' => __('Notification'),
            ],
            'warning' => [
                'color' => 'warning',
                'icon' => 'gmdi-warning-r',
                'text' => __('Attention recommended'),
            ],
            'critical' => [
                'color' => 'danger',
                'icon' => 'gmdi-warning-r',
                'text' => __('Attention required'),
            ],
        ];

        if (
            (! is_null($timeTillRefueling) && $timeTillRefueling < 10)
            || $maintenanceStatus['time'] < 31
            || $maintenanceStatus['distance'] < 1500
            || $timeTillApk < 31
            || (! is_null($timeTillAircoCheck) && $timeTillAircoCheck < 31)
            || $timeTillInsuranceEndDate < 31
        ) {
            return ! empty($item) ? $priorities['critical'][$item] : $priorities['critical'];
        }

        if (
            (! is_null($timeTillRefueling) && $timeTillRefueling < 30)
            || $maintenanceStatus['time'] < 62
            || $maintenanceStatus['distance'] < 3000
            || $timeTillApk < 62
            || (! is_null($timeTillAircoCheck) && $timeTillAircoCheck < 62)
            || $timeTillInsuranceEndDate < 62
        ) {
            return ! empty($item) ? $priorities['warning'][$item] : $priorities['warning'];
        }

        if (
            $timeTillInsuranceEndDate < 62
            || ($timeTillTaxEndDate > 0 && $timeTillTaxEndDate < 31)
        ) {
            return ! empty($item) ? $priorities['info'][$item] : $priorities['info'];
        }

        return ! empty($item) ? $priorities['success'][$item] : $priorities['success'];
    }

    /**
     * Get the user that owns the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the refuelings that the vehicle has
     */
    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class);
    }

    /**
     * Get the maintenances that the vehicle has
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get the insurances that the vehicle has
     */
    public function insurances(): HasMany
    {
        return $this->hasMany(Insurance::class);
    }

    /**
     * Get the taxes that the vehicle has
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function parkings(): HasMany
    {
        return $this->hasMany(Parking::class);
    }

    public function tolls(): HasMany
    {
        return $this->hasMany(Toll::class);
    }
}
