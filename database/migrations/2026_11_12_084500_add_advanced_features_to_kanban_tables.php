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
        if (Schema::hasTable('kanban_boards') && !Schema::hasColumn('kanban_boards', 'template')) {
            Schema::table('kanban_boards', function (Blueprint $table) {
                $table->string('template', 50)->nullable()->default('custom')->after('is_public');
            });
        }

        if (Schema::hasTable('kanban_columns') && !Schema::hasColumn('kanban_columns', 'wip_limit')) {
            Schema::table('kanban_columns', function (Blueprint $table) {
                $table->integer('wip_limit')->nullable()->after('is_done_column');
            });
        }

        if (Schema::hasTable('kanban_cards')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('kanban_cards', 'estimated_hours')) {
                    $table->decimal('estimated_hours', 6, 1)->nullable()->after('priority');
                }
                if (!Schema::hasColumn('kanban_cards', 'spent_hours')) {
                    $table->decimal('spent_hours', 6, 1)->nullable()->after('estimated_hours');
                }
                if (!Schema::hasColumn('kanban_cards', 'cover_image')) {
                    $table->string('cover_image', 255)->nullable()->after('attachments');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kanban_boards') && Schema::hasColumn('kanban_boards', 'template')) {
            Schema::table('kanban_boards', function (Blueprint $table) {
                $table->dropColumn('template');
            });
        }

        if (Schema::hasTable('kanban_columns') && Schema::hasColumn('kanban_columns', 'wip_limit')) {
            Schema::table('kanban_columns', function (Blueprint $table) {
                $table->dropColumn('wip_limit');
            });
        }

        if (Schema::hasTable('kanban_cards')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('kanban_cards', 'estimated_hours')) $cols[] = 'estimated_hours';
                if (Schema::hasColumn('kanban_cards', 'spent_hours')) $cols[] = 'spent_hours';
                if (Schema::hasColumn('kanban_cards', 'cover_image')) $cols[] = 'cover_image';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
