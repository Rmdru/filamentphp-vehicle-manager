<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refueling', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignId('vehicle_id');
            $table->date('date');
            $table->string('gas_station', 100);
            $table->string('fuel_type', 100);
            $table->float('amount');
            $table->float('unit_price');
            $table->float('total_price');
            $table->integer('mileage_begin')->nullable();
            $table->integer('mileage_end');
            $table->float('fuel_usage_onboard_computer')->nullable();
            $table->float('fuel_usage');
            $table->float('costs_per_kilometer');
            $table->string('tires', 100)->nullable();
            $table->json('climate_control')->nullable();
            $table->json('routes')->nullable();
            $table->string('driving_style', 100)->nullable();
            $table->text('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refueling');
    }
};
