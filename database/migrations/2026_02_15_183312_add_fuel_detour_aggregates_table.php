<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fuel_detour_aggregates')) {
            Schema::create('fuel_detour_aggregates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('vehicle_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('fuel_price_id')->constrained()->cascadeOnDelete();
                $table->integer('max_detour_only_fuel_costs');
                $table->integer('max_detour_all_costs');
                $table->timestamps();
                $table->unique(['vehicle_id', 'fuel_price_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_detour_aggregates');
    }
};
