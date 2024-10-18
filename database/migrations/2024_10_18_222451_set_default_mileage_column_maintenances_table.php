<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropColumns('maintenances', 'mileage_end');

        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('mileage_begin', 'mileage');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->integer('mileage')->nullable(false)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('mileage', 'mileage_begin');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->integer('mileage_end')->nullable();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->integer('mileage_begin')->nullable()->default(null)->change();
        });
    }
};
