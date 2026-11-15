<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_jam_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_jam_id')->constrained('dev_jams')->cascadeOnDelete();
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('question', 280);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('dev_jam_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('dev_jam_polls')->cascadeOnDelete();
            $table->string('label', 120);
            $table->unsignedInteger('votes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('dev_jam_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('dev_jam_polls')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('dev_jam_poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_jam_poll_votes');
        Schema::dropIfExists('dev_jam_poll_options');
        Schema::dropIfExists('dev_jam_polls');
    }
};
