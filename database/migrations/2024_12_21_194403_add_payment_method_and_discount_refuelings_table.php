<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('avg_speed');
            $table->string('discount', 255)->nullable()->after('avg_speed');
        });
    }

    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('discount');
        });
    }
};
