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
        Schema::create('ferries', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('vehicle_id');
            $table->string('start_location', 255);
            $table->string('end_location', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->float('price');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ferries');
    }
};
