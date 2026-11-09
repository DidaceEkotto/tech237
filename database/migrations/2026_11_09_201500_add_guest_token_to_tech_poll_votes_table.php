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
        if (Schema::hasTable('tech_poll_votes') && !Schema::hasColumn('tech_poll_votes', 'guest_token')) {
            Schema::table('tech_poll_votes', function (Blueprint $table) {
                $table->string('guest_token', 64)->nullable()->after('ip_address')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tech_poll_votes') && Schema::hasColumn('tech_poll_votes', 'guest_token')) {
            Schema::table('tech_poll_votes', function (Blueprint $table) {
                $table->dropColumn('guest_token');
            });
        }
    }
};
