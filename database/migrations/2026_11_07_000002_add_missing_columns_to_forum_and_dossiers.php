<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('forum_topics')) {
            Schema::table('forum_topics', function (Blueprint $table) {
                if (!Schema::hasColumn('forum_topics', 'is_solved')) {
                    $table->boolean('is_solved')->default(false)->after('views_count');
                }
                if (!Schema::hasColumn('forum_topics', 'is_closed')) {
                    $table->boolean('is_closed')->default(false)->after('is_solved');
                }
            });
        }

        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (!Schema::hasColumn('dossiers', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_featured');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('forum_topics')) {
            Schema::table('forum_topics', function (Blueprint $table) {
                if (Schema::hasColumn('forum_topics', 'is_solved')) {
                    $table->dropColumn('is_solved');
                }
                if (Schema::hasColumn('forum_topics', 'is_closed')) {
                    $table->dropColumn('is_closed');
                }
            });
        }

        if (Schema::hasTable('dossiers')) {
            Schema::table('dossiers', function (Blueprint $table) {
                if (Schema::hasColumn('dossiers', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
