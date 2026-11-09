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
        if (!Schema::hasTable('kanban_boards')) {
            Schema::create('kanban_boards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kanban_columns')) {
            Schema::create('kanban_columns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
                $table->string('title');
                $table->string('color')->default('#3b82f6');
                $table->integer('order')->default(0);
                $table->boolean('is_done_column')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kanban_cards')) {
            Schema::create('kanban_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('column_id')->constrained('kanban_columns')->cascadeOnDelete();
                $table->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
                $table->string('title');
                $table->longText('description')->nullable();
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->date('due_date')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->json('labels')->nullable();
                $table->json('checklist')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('devlog_published')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_cards');
        Schema::dropIfExists('kanban_columns');
        Schema::dropIfExists('kanban_boards');
    }
};
