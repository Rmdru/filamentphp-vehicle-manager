<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('vehicle_id');
            $table->datetime('datetime');
            $table->string('location', 255);
            $table->string('type', 255);
            $table->longtext('description');
            $table->boolean('guilty')->default(0);
            $table->json('situation')->nullable();
            $table->float('damage_own')->nullable();
            $table->float('damage_own_insured')->nullable();
            $table->float('damage_others')->nullable();
            $table->float('damage_others_insured')->nullable();
            $table->float('total_price')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
