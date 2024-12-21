<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('factory_specification_fuel_consumption');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->json('specifications')->after('status')->nullable();
            $table->json('fuel_types')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->double('factory_specification_fuel_consumption')->after('engine');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('specifications');
            $table->dropColumn('fuel_types');
        });
    }
};
