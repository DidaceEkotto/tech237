<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rubriques')) {
            Schema::table('rubriques', function (Blueprint $table) {
                if (!Schema::hasColumn('rubriques', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('icon');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rubriques')) {
            Schema::table('rubriques', function (Blueprint $table) {
                if (Schema::hasColumn('rubriques', 'banner_image')) {
                    $table->dropColumn('banner_image');
                }
            });
        }
    }
};
