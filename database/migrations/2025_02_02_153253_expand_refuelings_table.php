<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->boolean('service_by_attendant')->after('payment_method')->nullable();
            $table->integer('charge_time')->after('payment_method')->nullable();
            $table->string('country', 50)->after('date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('refuelings', function (Blueprint $table) {
            $table->dropColumn('service_by_attendant');
            $table->dropColumn('charge_time');
            $table->dropColumn('country');
        });
    }
};
