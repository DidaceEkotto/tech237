<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kanban_cards')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('kanban_cards', 'is_completed')) {
                    $table->boolean('is_completed')->default(false)->after('order');
                }
                if (!Schema::hasColumn('kanban_cards', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->after('is_completed');
                }
                if (!Schema::hasColumn('kanban_cards', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('is_archived');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kanban_cards')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                if (Schema::hasColumn('kanban_cards', 'is_completed')) {
                    $table->dropColumn('is_completed');
                }
                if (Schema::hasColumn('kanban_cards', 'is_archived')) {
                    $table->dropColumn('is_archived');
                }
                if (Schema::hasColumn('kanban_cards', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
            });
        }
    }
};
