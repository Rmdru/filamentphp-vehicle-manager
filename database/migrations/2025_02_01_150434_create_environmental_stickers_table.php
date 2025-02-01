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
        Schema::create('environmental_stickers', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('vehicle_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->float('price');
            $table->string('country', 255)->nullable();
            $table->text('areas')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environmental_stickers');
    }
};
