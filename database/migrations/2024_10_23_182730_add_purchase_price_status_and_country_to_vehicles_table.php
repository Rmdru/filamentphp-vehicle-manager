<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('status', 20)->default('driveable')->after('is_private');
            $table->float('purchase_price')->nullable()->after('purchase_date');
            $table->string('country_registration', 255)->after('powertrain');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('purchase_price');
            $table->dropColumn('country_registration');
        });
    }
};
