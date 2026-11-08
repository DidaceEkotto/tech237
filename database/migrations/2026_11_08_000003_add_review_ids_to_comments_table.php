<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'game_review_id')) {
                $table->foreignId('game_review_id')->nullable()->after('project_id')->constrained('game_reviews')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('comments', 'app_review_id')) {
                $table->foreignId('app_review_id')->nullable()->after('game_review_id')->constrained('app_reviews')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'game_review_id')) {
                $table->dropConstrainedForeignId('game_review_id');
            }
            if (Schema::hasColumn('comments', 'app_review_id')) {
                $table->dropConstrainedForeignId('app_review_id');
            }
        });
    }
};
