<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id');
            $table->string('type', 50);
            $table->string('payment_circumstances', 50)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('road_type', 50)->nullable();
            $table->json('road')->nullable();
            $table->string('start_location', 100);
            $table->string('end_location', 100)->nullable();
            $table->date('date');
            $table->float('price');
            $table->string('toll_company', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tolls');
    }
};
