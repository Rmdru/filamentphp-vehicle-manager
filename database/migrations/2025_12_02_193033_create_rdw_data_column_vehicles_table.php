<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicles', 'rdw_data')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->json('rdw_data')->nullable()->default(json_encode([]))->after('privacy_settings');
            });
        }
    }
    
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'rdw_data')) {
            Schema::dropColumns('rdw_data');
        }
    }
};
