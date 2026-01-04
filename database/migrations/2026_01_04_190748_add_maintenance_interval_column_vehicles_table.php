<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicles', 'maintenance_interval')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->integer('maintenance_interval')->default(15000)->after('tank_capacity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'maintenance_interval')) {
            Schema::table('vehicles', function (Blueprint $table): void {
                $table->dropColumn('maintenance_interval');
            });
        }
    }
};
