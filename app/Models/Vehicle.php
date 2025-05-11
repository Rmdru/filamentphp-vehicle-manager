<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceTypeMaintenance;
use App\Enums\VehicleStatus;
use App\Services\VehicleCostsService;
use App\Support\Cost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
        'mileage_start',
        'mileage_latest',
        'purchase_date',
        'construction_date',
        'purchase_price',
        'license_plate',
        'powertrain',
        'country_registration',
        'status',
        'fuel_types',
        'tank_capacity',
        'specifications',
        'notifications',
        'privacy_settings',
    ];

    protected $casts = [
        'purchase_date' => 'date:Y-m-d',
        'construction_date' => 'date:Y-m-d',
        'fuel_types' => 'array',
        'specifications' => 'array',
        'notifications' => 'array',
        'privacy_settings' => 'array',
    ];

    protected $appends = [
        'fuel_status',
        'maintenance_status',
        'apk_status',
        'airco_check_status',
        'insurance_status',
        'tax_status',
        'washing_status',
        'tire_pressure_check_status',
        'liquids_check_status',
    ];

    protected static function booted()
    {
        static::addGlobalScope('ownVehicles', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }

    public function scopeSelected(Builder $query): void
    {
        $vehicleId = session('vehicle_id') ?? Vehicle::latest()->first()->id;

        session(['vehicle_id' => $vehicleId]);

        $query->where([
            'id' => session('vehicle_id'),
            'user_id' => Auth::id(),
        ]);
    }

    public function scopeOnlyDriveable(Builder $query): void
    {
        $query->where([
            'status' => 'drivable',
        ]);
    }

    public function getImageUrlAttribute()
    {
        $url = url(route('vehicle.image', ['vehicle' => $this->id]));

        return Http::head($url)->successful() ? $url : null;
    }

    public function getFullNameAttribute(): string
    {
        $brands = config('vehicles.brands');

        return $brands[$this->brand] . ' ' . $this->model;
    }

    public function getFullNameWithLicensePlateAttribute(): string
    {
        $brands = config('vehicles.brands');

        return $brands[$this->brand] . ' ' . $this->model . ' (' . $this->license_plate . ')';
    }

    public function getFuelStatusAttribute(): ?int
    {
        if ($this->refuelings->isNotEmpty() && $this->refuelings->where('fuel_type', 'Unleaded 95 (E10)')->count() > 0) {
            $latestRefueling = $this->refuelings->sortByDesc('date')->first();

            if (! empty($latestRefueling) && $latestRefueling->fuel_type === 'Unleaded 95 (E10)') {
                $diff = Carbon::parse($latestRefueling->date)->addMonths(2)->diffInDays(now());
                return (int) max(0, $diff - ($diff * 2));
            }
        }

        return null;
    }

    public function getMaintenanceStatusAttribute(): array
    {
        $maintenanceTypes = ['small_maintenance', 'maintenance', 'big_maintenance'];

        $latestMaintenance = $this->maintenances->whereIn('type_maintenance', $maintenanceTypes)->sortByDesc('date')->first();

        if (empty($latestMaintenance) || ! in_array($latestMaintenance->type_maintenance, $maintenanceTypes)) {
            return [];
        }

        if (! empty($latestMaintenance) && in_array($latestMaintenance->type_maintenance, $maintenanceTypes)) {
            $maintenanceDate = Carbon::parse($latestMaintenance->date ?? now())->addYear();
            $maintenanceDiff = $maintenanceDate->diffInDays(now());
            $timeDiffHumans = $maintenanceDate->isFuture() ? $maintenanceDate->diffForHumans() : __('Now');

            $timeTillMaintenance = max(0, $maintenanceDiff - ($maintenanceDiff * 2));

            $distanceTillMaintenance = 15000 + $latestMaintenance->mileage - $this->mileage_latest;
        }

        return [
            'time' => $timeTillMaintenance,
            'timeDiffHumans' => $timeDiffHumans,
            'distance' => $distanceTillMaintenance,
        ];
    }

    public function getApkStatusAttribute(): array
    {
        if ($this->maintenances->where('apk', true)->isEmpty()) {
            return [];
        }

        if ($this->maintenances->where('apk', true)->isNotEmpty()) {
            $latestApk = $this->maintenances->where('apk', true)->sortByDesc('date')->first();

            $apkDate = Carbon::parse($latestApk->date ?? now())->addYear();
            $apkDiff = $apkDate->diffInDays(now());
            $timeDiffHumans = $apkDate->isFuture() ? $apkDate->diffForHumans() : __('Now');

            $timeTillApk = max(0, $apkDiff - ($apkDiff * 2));
        }

        return [
            'time' => $timeTillApk,
            'timeDiffHumans' => $timeDiffHumans,
        ];
    }

    public function getAircoCheckStatusAttribute(): array
    {
        if ($this->maintenances->where('airco_check', true)->isNotEmpty()) {
            $latestAircoCheck = $this->maintenances->where('airco_check', true)->sortByDesc('date')->first();

            $aircoCheckDate = Carbon::parse($latestAircoCheck->date)->addYears(2);
            $timeTillAircoCheckDiff = $aircoCheckDate->diffInDays(now());
            $timeTillAircoCheck = max(0, $timeTillAircoCheckDiff - ($timeTillAircoCheckDiff * 2));
            $timeDiffHumans = $aircoCheckDate->isFuture() ? $aircoCheckDate->diffForHumans() : __('Now');

            return [
                'time' => $timeTillAircoCheck,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getTirePressureCheckStatusAttribute(): array
    {
        if ($this->maintenances->where('type_maintenance', MaintenanceTypeMaintenance::TirePressureChecked->value)->isNotEmpty()) {
            $latest = $this->maintenances->where('type_maintenance', MaintenanceTypeMaintenance::TirePressureChecked->value)->sortByDesc('date')->first();

            $date = Carbon::parse($latest->date)->addMonths(2);
            $timeTillDiff = $date->diffInDays(now());
            $timeTill = max(0, $timeTillDiff - ($timeTillDiff * 2));
            $timeDiffHumans = $date->isFuture() ? $date->diffForHumans() : __('Now');

            return [
                'time' => $timeTill,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getLiquidsCheckStatusAttribute(): array
    {
        if ($this->maintenances->where('type_maintenance', 'liquids_check')->isNotEmpty()) {
            $latest = $this->maintenances->where('type_maintenance', 'liquids_check')->sortByDesc('date')->first();

            $date = Carbon::parse($latest->date)->addMonths(2);
            $timeTillDiff = $date->diffInDays(now());
            $timeTill = max(0, $timeTillDiff - ($timeTillDiff * 2));
            $timeDiffHumans = $date->isFuture() ? $date->diffForHumans() : __('Now');

            return [
                'time' => $timeTill,
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
            $timeDiffHumans = $insurance->end_date->isFuture() ? $insurance->end_date->diffForHumans() : __('Now');

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
            $timeDiffHumans = $tax->end_date->isFuture() ? $tax->end_date->diffForHumans(): __('Now');

            return [
                'time' => $timeTillTaxEndDate,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

        return [];
    }

    public function getWashingStatusAttribute(): array
    {
        if ($this->reconditionings->isEmpty()) {
            return [];
        }

        if ($this->reconditionings->isNotEmpty()) {
            $latestWashDate = $this->reconditionings->filter(function ($item) {
                $types = $item->type;
                return collect($types)->contains(function ($type) {
                    return str_contains($type, 'carwash') || str_contains($type, 'exterior_cleaning');
                });
            })->sortByDesc('date')
                ->first();

            $washDate = Carbon::parse($latestWashDate->date ?? now())->addMonth();
            $washDiff = $washDate->diffInDays(now());
            $timeDiffHumans = $washDate->isFuture() ? $washDate->diffForHumans() : __('Now');

            $timeTillWash = max(0, $washDiff - ($washDiff * 2));
        }

        return [
            'time' => $timeTillWash,
            'timeDiffHumans' => $timeDiffHumans,
        ];
    }

    public function calculateMonthlyCosts(string $startDate = '', string $endDate = ''): array
    {
        $vehicleId = Vehicle::selected()->first()->id;
        $costTypes = Cost::types();

        if (empty($startDate) || empty($endDate)) {
            $vehicleCostsService = new VehicleCostsService;
            $dateRange = $vehicleCostsService->getMonths($vehicleId);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $monthlyCosts = [];
        $labels = [];

        foreach ($costTypes as $label => $config) {
            $model = $config['model'];
            $priceField = $config['priceField'];
            $monthly = $config['monthly'] ?? false;
            $dateColumn = $config['dateColumn'] ?? 'date';

            if (empty($monthly)) {
                $data = $model::where('vehicle_id', $vehicleId)
                    ->whereBetween($dateColumn, [$startDate, $endDate])
                    ->get()
                    ->groupBy(function ($item) use ($dateColumn) {
                        return Carbon::parse($item->$dateColumn)->format('Y-m');
                    })
                    ->map(function ($row) use ($priceField) {
                        return $row->sum($priceField);
                    });

                foreach ($data as $month => $value) {
                    if (! isset($monthlyCosts[$month])) {
                        $monthlyCosts[$month] = [];
                    }

                    if (! isset($monthlyCosts[$month][$label])) {
                        $monthlyCosts[$month][$label] = 0;
                    }

                    $monthlyCosts[$month][$label] += $value;
                }

                if (empty($labels)) {
                    $labels = collect();
                    $currentMonth = $startDate->copy();
                    while ($currentMonth <= $endDate) {
                        $labels->push(str($currentMonth->isoFormat('MMM YY'))->ucfirst());
                        $currentMonth->addMonth();
                    }
                }
            }

            if (! empty($monthly)) {
                $records = $model::where('vehicle_id', $vehicleId)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->get();

                foreach ($records as $record) {
                    $start = Carbon::parse($record->start_date)->startOfMonth();
                    $end = Carbon::parse($record->end_date)->endOfMonth();
                    $paymentDay = $record->payment_day ?? 1;
                    $monthlyAmount = $record->$priceField;

                    while ($start <= $end) {
                        $month = $start->isoFormat('Y-MM');

                        if ($start->between($startDate, $endDate)) {
                            if (!isset($monthlyCosts[$month])) {
                                $monthlyCosts[$month] = [];
                            }

                            if (!isset($monthlyCosts[$month][$label])) {
                                $monthlyCosts[$month][$label] = 0;
                            }

                            $paymentDate = Carbon::createFromFormat('Y-m-d', "{$start->year}-{$start->month}-{$paymentDay}");
                            if ($paymentDate->between($startDate, $endDate)) {
                                $monthlyCosts[$month][$label] += $monthlyAmount;
                            }
                        }

                        $start->addMonth();
                    }
                }
            }
        }

        $allMonths = collect();
        $currentMonth = $startDate->copy();
        while ($currentMonth <= $endDate) {
            $allMonths->push($currentMonth->isoFormat('Y-MM'));
            $currentMonth->addMonth();
        }

        foreach ($allMonths as $month) {
            if (! isset($monthlyCosts[$month])) {
                $monthlyCosts[$month] = [];
            }

            foreach ($costTypes as $label => $config) {
                if (! isset($monthlyCosts[$month][$label])) {
                    $monthlyCosts[$month][$label] = 0;
                }
            }
        }

        $monthlyCosts = collect($monthlyCosts)->sortKeys()->toArray();

        return [
            'monthlyCosts' => $monthlyCosts,
            'labels' => $labels,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(Insurance::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function parking(): HasMany
    {
        return $this->hasMany(Parking::class);
    }

    public function toll(): HasMany
    {
        return $this->hasMany(Toll::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function reconditionings(): HasMany
    {
        return $this->hasMany(Reconditioning::class);
    }

    public function vignettes(): HasMany
    {
        return $this->hasMany(Vignette::class);
    }

    public function environmentalStickers(): HasMany
    {
        return $this->hasMany(EnvironmentalSticker::class);
    }

    public function ferries(): HasMany
    {
        return $this->hasMany(Ferry::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function accidents(): HasMany
    {
        return $this->hasMany(Accident::class);
    }
}
