<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->integer('invoice_day')->default(1)->after('price');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->integer('invoice_day')->default(1)->after('price');
        });
    }

    public function down(): void
    {
        Schema::dropColumns('insurances', ['invoice_day']);
        Schema::dropColumns('taxes', ['invoice_day']);
    }
};
