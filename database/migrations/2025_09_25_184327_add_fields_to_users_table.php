<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('profile_photo_path');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->json('preferences')->nullable()->after('last_login_at');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_photo_path', 'is_active', 'last_login_at', 'preferences']);
            $table->dropSoftDeletes();
        });
    }
};
