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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('washed', 'airco_check');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->boolean('airco_check')->default('0')->change();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->json('tasks')->after('mileage_end')->nullable();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignUuid('vehicle_id')->change();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->uuid('id')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('airco_check', 'washed');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->json('washed')->nullable()->change();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('tasks');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->uuid('id')->dropUnique();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->change();
        });
    }
};
