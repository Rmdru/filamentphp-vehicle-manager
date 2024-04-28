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
            $table->uuid('id')->unique()->change();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->uuid('id')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->uuid('id')->dropUnique();
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->uuid('id')->dropUnique();
        });
    }
};
