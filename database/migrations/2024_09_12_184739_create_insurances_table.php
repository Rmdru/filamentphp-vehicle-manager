<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    use HasUuids;
    use SoftDeletes;

    public function up(): void
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('vehicle_id');
            $table->string('insurance_company', 50);
            $table->string('type', 50);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->float('price');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
