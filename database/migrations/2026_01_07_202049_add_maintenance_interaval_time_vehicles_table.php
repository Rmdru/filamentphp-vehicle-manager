<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vehicles', 'maintenance_interval')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->renameColumn('maintenance_interval', 'maintenance_interval_distance');
            });
        }

        if (! Schema::hasColumn('vehicles', 'maintenance_interval_time')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->integer('maintenance_interval_time')->default(12)->after('maintenance_interval_distance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'maintenance_interval_distance')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->renameColumn('maintenance_interval_distance', 'maintenance_interval');
            });
        }

        if (Schema::hasColumn('vehicles', 'maintenance_interval_time')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->dropColumn('maintenance_interval_time');
            });
        }
    }
};
