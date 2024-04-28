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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('powertrain', 50)->nullable()->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->string('fuel_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('powertrain')->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->integer('fuel_type')->change();
        });
    }
};
