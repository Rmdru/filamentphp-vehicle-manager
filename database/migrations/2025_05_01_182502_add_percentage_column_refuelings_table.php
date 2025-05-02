<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            if (! Schema::hasColumn('refuelings', 'percentage')) {
                $table->float('percentage')->default(100)->after('mileage_end');
            }
        });

        DB::table('refuelings')->update(['percentage' => 100]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            if (Schema::hasColumn('refuelings', 'percentage')) {
                $table->dropColumn('percentage');
            }
        });
    }
};
