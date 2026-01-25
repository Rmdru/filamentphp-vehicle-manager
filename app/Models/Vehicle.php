<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceTypeMaintenance;
use App\Services\OpenMeteoService;
use App\Services\RdwService;
use App\Traits\VehicleStats;
use App\Services\VehicleCostsService;
use App\Support\Cost;
use Carbon\Carbon;
use Filament\Models\Contracts\HasName;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\LocationByIp;
use Illuminate\Support\Facades\Cache;

class Vehicle extends Model implements HasName
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use VehicleStats;
    use LocationByIp;

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
        'maintenance_interval_distance',
        'maintenance_interval_time',
        'specifications',
        'notifications',
        'privacy_settings',
        'rdw_data',
    ];

    protected $casts = [
        'purchase_date' => 'date:Y-m-d',
        'construction_date' => 'date:Y-m-d',
        'fuel_types' => 'array',
        'specifications' => 'array',
        'notifications' => 'array',
        'privacy_settings' => 'array',
        'rdw_data' => 'array',
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

    private array $historicalMinTemps = [];

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function scopeOwnVehicles(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }

    public function scopeOnlyDriveable(Builder $query): void
    {
        $query->where([
            'status' => 'drivable',
        ]);
    }

    public function getImagePathAttribute(): string
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        foreach ($extensions as $extension) {
            return 'vehicles/' . $this->id . '.' . $extension;
        }

        return '';
    }

    public function getImageExistsAttribute(): bool
    {
        return Storage::disk('private')->exists($this->image_path);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_exists) {
            return url(route('vehicle.image', ['vehicle' => $this->id]));
        }
        
        return '';
    }

    public function getFullNameAttribute(): string
    {
        $brands = config('vehicles.brands');

        $brandName = $brands[$this->brand] ?? $this->brand;

        return $brandName . ' ' . $this->model;
    }

    public function getFullNameWithLicensePlateAttribute(): string
    {
        $brands = config('vehicles.brands');

        return $brands[$this->brand] . ' ' . $this->model . ' (' . $this->license_plate . ')';
    }

    public function getLicensePlateNormalizedAttribute(): string
    {
        return str_replace([' ', '-', '+'], '', strtoupper($this->license_plate));
    }

    public function getFuelStatusAttribute(): ?array
    {
        if ($this->refuelings->isNotEmpty() && $this->refuelings->where('fuel_type', 'Unleaded 95 (E10)')->count() > 0) {
            $latestRefueling = $this->refuelings->sortByDesc('date')->first();

            if (! empty($latestRefueling) && $latestRefueling->fuel_type === 'Unleaded 95 (E10)') {
                $diff = Carbon::parse($latestRefueling->date)->addMonths(2)->diffInDays(now());
                return [
                    'time' => (int) max(0, $diff - ($diff * 2))
                ];
            }
        }

        return [];
    }

    public function getPeriodicSuperPlusAttribute(): array
    {
        $fuelTypes = [
            'Unleaded 95 (E10)',
            'Unleaded 95 (E5)',
        ];

        if ($this->refuelings->isNotEmpty()) {
            return [
                 'recordCount' => $this->refuelings()
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->whereIn('fuel_type', $fuelTypes)
                    ->count(),
            ];
        }

        return [
            'recordCount' => 0,
        ];
    }

    public function getMaintenanceStatusAttribute(): array
    {
        $maintenanceTypes = ['small_maintenance', 'maintenance', 'big_maintenance'];

        $latestMaintenance = $this->maintenances->whereIn('type_maintenance', $maintenanceTypes)->sortByDesc('date')->first();

        if (
            empty($latestMaintenance)
            || ! in_array($latestMaintenance->type_maintenance, $maintenanceTypes)
            || empty($this->maintenance_interval_distance)
        ) {
            return [];
        }

        if (! empty($latestMaintenance) && in_array($latestMaintenance->type_maintenance, $maintenanceTypes)) {
            $maintenanceDate = Carbon::parse($latestMaintenance->date ?? now())->addMonths($this->maintenance_interval_time);
            $maintenanceDiff = $maintenanceDate->diffInDays(now());
            $timeDiffHumans = $maintenanceDate->isFuture() ? $maintenanceDate->diffForHumans() : __('Now');

            $timeTillMaintenance = $maintenanceDiff - ($maintenanceDiff * 2);

            $distanceTillMaintenance = $this->maintenance_interval_distance + $latestMaintenance->mileage - $this->mileage_latest;
        }

        $daysTillDistanceDeadline = (int) $this->calculateAverageMonthlyDistance() > 30.44 ? $distanceTillMaintenance / ($this->calculateAverageMonthlyDistance() / 30.44) : $distanceTillMaintenance;
        $daysTillTimeDeadline = (int) Carbon::now()->daysInYear - $timeTillMaintenance;
        $timeTillDistanceDeadlineHumans = now()->addDays($daysTillDistanceDeadline)->diffForHumans();
        $minDaysTillDeadline = min($daysTillDistanceDeadline, $daysTillTimeDeadline);

        return [
            'time' => $timeTillMaintenance,
            'timeDiffHumans' => $timeDiffHumans,
            'distance' => $distanceTillMaintenance,
            'daysTillDistanceDeadline' => $daysTillDistanceDeadline,
            'timeTillDistanceDeadlineHumans' => $timeTillDistanceDeadlineHumans,
            'daysTillTimeDeadline' => $daysTillTimeDeadline,
            'minDaysTillDeadline' => $minDaysTillDeadline,
        ];
    }

    public function getApkStatusAttribute(): array
    {
        $rdwData = $this->rdw_data;

        if (! empty($rdwData['vervaldatum_apk_dt'])) {
            $apkDate = Carbon::parse($rdwData['vervaldatum_apk_dt']);
            $apkDiff = $apkDate->diffInDays(now());
            $timeDiffHumans = $apkDate->isFuture() ? $apkDate->diffForHumans() : __('Now');

            $timeTillApk = max(0, $apkDiff - ($apkDiff * 2));

            return [
                'time' => $timeTillApk,
                'timeDiffHumans' => $timeDiffHumans,
            ];
        }

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
        $rdwData = $this->rdw_data;

        if (! empty($rdwData['wam_verzekerd']) && $rdwData['wam_verzekerd'] !== 'Ja') {
            return [
                'time' => -1,
                'timeDiffHumans' => __('Now'),
            ];
        }

        if ($this->insurances->isEmpty()) {
            return [];
        }

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

    private function setHistoricalMinTemps(): void
    {
        if (! empty($this->historicalMinTemps)) {
            return;
        }

        $ipLocation = $this->getLocationDataByIp(request()->ip());
        $latestWash = (new Reconditioning)->latestWash($this->reconditionings);

        if (empty($latestWash)) {
            return;
        }
        
        $this->historicalMinTemps = (new OpenMeteoService)->fetchHistoricalMinTempsInDateRange($ipLocation, $latestWash->date->format('Y-m-d'), now()->format('Y-m-d'));

        Cache::store('database')->put('vehicle_' . $this->id . '_historical_min_temps', $this->historicalMinTemps, now()->addDay());
    }

    private function getHistoricalMinTemps(): array
    {
        if (Cache::has('vehicle_' . $this->id . '_historical_min_temps')) {
            return Cache::get('vehicle_' . $this->id . '_historical_min_temps');
        }

        if (empty($this->historicalMinTemps)) {
            $this->setHistoricalMinTemps();
        }

        return $this->historicalMinTemps;
    }

    public function getWashingStatusAttribute(): array
    {
        $latestWash = (new Reconditioning)->latestWash($this->reconditionings);

        if (empty($latestWash)) {
            return [];
        }

        $washDate = Carbon::parse($latestWash->date ?? now())->addMonth();
        $washDiff = $washDate->diffInDays(now());
        $timeDiffHumans = $washDate->isFuture() ? $washDate->diffForHumans() : __('Now');

        $timeTillWash = max(0, $washDiff - ($washDiff * 2));

        return [
            'time' => $timeTillWash,
            'timeDiffHumans' => $timeDiffHumans,
        ];
    }

    public function getCarwashStatusAttribute(): array
    {
        if ($this->reconditionings->isEmpty()) {
            return [];
        }

        $washingStatus = $this->washing_status;

        if (empty($washingStatus) || (! empty($washingStatus) && $washingStatus['time'] >= 10)) {
            return [];
        }
        
        $historicalMinTemps = $this->getHistoricalMinTemps();

        if (empty($historicalMinTemps) || min($historicalMinTemps['daily']['temperature_2m_min']) >= 4) {
            return [];
        }
        
        return $washingStatus;
    }

    public function getSelfWashingStatusAttribute(): array
    {
        if ($this->reconditionings->isEmpty()) {
            return [];
        }

        $washingStatus = $this->washing_status;

        if (empty($washingStatus) || (! empty($washingStatus) && $washingStatus['time'] >= 10)) {
            return [];
        }
        
        $historicalMinTemps = $this->getHistoricalMinTemps();

        if (empty($historicalMinTemps) || min($historicalMinTemps['daily']['temperature_2m_min']) < 4) {
            return [];
        }
        
        return $washingStatus;
    }

    public function getRecallStatusAttribute(): array
    {
        $openRecalls = $this->rdw_data['open_recalls'] ?? [];

        if (empty($openRecalls)) {
            return [
                'recordCount' => 0,
                'hasModal' => false,
                'data' => [],
            ];
        }

        return [
            'recordCount' => count($openRecalls),
            'hasModal' => true,
            'data' => $openRecalls,
        ];
    }

    public function calculateMonthlyCosts(string $startDate = '', string $endDate = ''): array
    {
        $vehicleId = Filament::getTenant()->id;
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

    public function parkings(): HasMany
    {
        return $this->hasMany(Parking::class);
    }

    public function tolls(): HasMany
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
