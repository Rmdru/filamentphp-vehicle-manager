<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'is_private')) {
                $table->dropColumn('is_private');
            }

            if (! Schema::hasColumn('vehicles', 'privacy_settings')) {
                $table->json('privacy_settings')->default(json_encode([]))->after('notifications');
            }
        });

        $privacySettings = config('default_privacy_settings');
    
        $privacySettings = json_encode(config('default_privacy_settings', []));
        DB::table('vehicles')->update(['privacy_settings' => $privacySettings]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'privacy_settings')) {
                $table->dropColumn('privacy_settings');
            }
            
            if (! Schema::hasColumn('vehicles', 'is_private')) {
                $table->boolean('is_private')->default(0)->after('notifications');
            }
        });
    }
};
