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
            $table->integer('mileage_begin')->nullable(false)->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->text('comments')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->integer('mileage_begin')->nullable()->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->text('comments')->nullable(false)->change();
        });
    }
};
