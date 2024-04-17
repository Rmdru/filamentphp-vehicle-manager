<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasUuids;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('brand', 50);
            $table->string('model', 50);
            $table->string('version', 50);
            $table->string('engine', 50)->nullable();
            $table->float('factory_specification_fuel_consumption');
            $table->integer('mileage_start')->nullable();
            $table->integer('mileage_latest')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('license_plate', 20)->nullable();
            $table->string('fuel_type', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
