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
        Schema::table('refuelings', function (Blueprint $table) {
            $table->foreignUuid('vehicle_id')->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->renameColumn('fuel_usage', 'fuel_consumption');
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->renameColumn('fuel_usage_onboard_computer', 'fuel_consumption_onboard_computer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->renameColumn('fuel_consumption', 'fuel_usage');
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->renameColumn('fuel_consumption_onboard_computer', 'fuel_usage_onboard_computer');
        });
    }
};
