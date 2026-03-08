<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('date');
            $table->index(['vehicle_id', 'date']);
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('date');
            $table->index(['vehicle_id', 'date']);
        });

        Schema::table('insurances', function (Blueprint $table) {
            $table->index('vehicle_id');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->index('vehicle_id');
        });

        Schema::table('parking', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('toll', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('date');
            $table->index('payed');
            $table->index(['vehicle_id', 'payed', 'date']);
            $table->index('deleted_at');
        });

        Schema::table('reconditionings', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('vignettes', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('environmental_stickers', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('ferries', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('accidents', function (Blueprint $table) {
            $table->index('vehicle_id');
            $table->index('deleted_at');
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('completed_at');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('license_plate');
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['vehicle_id', 'date']);
        });

        Schema::table('refuelings', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['vehicle_id', 'date']);
        });

        Schema::table('insurances', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
        });

        Schema::table('parking', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('toll', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('fines', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['payed']);
            $table->dropIndex(['vehicle_id', 'payed', 'date']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('reconditionings', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('vignettes', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('environmental_stickers', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('ferries', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('accidents', function (Blueprint $table) {
            $table->dropIndex(['vehicle_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['completed_at']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['license_plate']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
    }
};
