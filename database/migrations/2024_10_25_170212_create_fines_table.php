<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id');
            $table->string('type', 50);
            $table->string('fact', 50);
            $table->text('description')->nullable();
            $table->string('icon', 30)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('road', 100)->nullable();
            $table->string('road_type', 50)->nullable();
            $table->float('road_distance_marker')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('provider', 100);
            $table->date('date');
            $table->boolean('fine')->default(0);
            $table->float('price')->nullable();
            $table->boolean('payed')->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->json('sanctions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
