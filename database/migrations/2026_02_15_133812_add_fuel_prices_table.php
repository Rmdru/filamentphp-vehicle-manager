<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fuel_prices')) {
            Schema::create('fuel_prices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->date('date');
                $table->string('country', 20);
                $table->string('fuel_type', 20);
                $table->decimal('price', 8, 3);
                $table->timestamps();
                $table->unique(['date', 'country', 'fuel_type']);
                $table->index(['date', 'country', 'fuel_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
