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
            $table->softDeletes();
        });

        Schema::table('refueling', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('refueling', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};