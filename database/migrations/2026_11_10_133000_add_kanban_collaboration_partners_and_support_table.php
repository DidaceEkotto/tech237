<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_support to user_messages
        if (Schema::hasTable('user_messages') && !Schema::hasColumn('user_messages', 'is_support')) {
            Schema::table('user_messages', function (Blueprint $table) {
                $table->boolean('is_support')->default(false)->after('is_read');
            });
        }

        // 2. Add attachments to kanban_cards
        if (Schema::hasTable('kanban_cards') && !Schema::hasColumn('kanban_cards', 'attachments')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                $table->json('attachments')->nullable()->after('checklist');
            });
        }

        // 3. Kanban Board Members
        if (!Schema::hasTable('kanban_board_members')) {
            Schema::create('kanban_board_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('member'); // member, admin, viewer
                $table->timestamps();

                $table->unique(['board_id', 'user_id']);
            });
        }

        // 4. Kanban Card Comments
        if (!Schema::hasTable('kanban_card_comments')) {
            Schema::create('kanban_card_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('card_id')->constrained('kanban_cards')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('comment');
                $table->timestamps();
            });
        }

        // 5. Kanban Card Activities
        if (!Schema::hasTable('kanban_card_activities')) {
            Schema::create('kanban_card_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
                $table->foreignId('card_id')->nullable()->constrained('kanban_cards')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type')->default('action');
                $table->text('description');
                $table->timestamps();
            });
        }

        // 6. Partners / Sponsors
        if (!Schema::hasTable('partners')) {
            Schema::create('partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('logo');
                $table->string('website_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('kanban_card_activities');
        Schema::dropIfExists('kanban_card_comments');
        Schema::dropIfExists('kanban_board_members');

        if (Schema::hasTable('kanban_cards') && Schema::hasColumn('kanban_cards', 'attachments')) {
            Schema::table('kanban_cards', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }

        if (Schema::hasTable('user_messages') && Schema::hasColumn('user_messages', 'is_support')) {
            Schema::table('user_messages', function (Blueprint $table) {
                $table->dropColumn('is_support');
            });
        }
    }
};
