<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'social_facebook')) {
                $table->string('social_facebook')->nullable();
            }
            if (!Schema::hasColumn('admins', 'social_twitter')) {
                $table->string('social_twitter')->nullable();
            }
            if (!Schema::hasColumn('admins', 'social_linkedin')) {
                $table->string('social_linkedin')->nullable();
            }
            if (!Schema::hasColumn('admins', 'social_instagram')) {
                $table->string('social_instagram')->nullable();
            }
            if (!Schema::hasColumn('admins', 'social_tiktok')) {
                $table->string('social_tiktok')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['social_facebook', 'social_twitter', 'social_linkedin', 'social_instagram', 'social_tiktok']);
        });
    }
};
