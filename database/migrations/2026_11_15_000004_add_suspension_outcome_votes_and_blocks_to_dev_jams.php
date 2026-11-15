<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dev_jams', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('status');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
        });

        Schema::create('dev_jam_outcome_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_jam_id')->constrained('dev_jams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vote', 10); // 'yes' | 'no'
            $table->timestamps();

            $table->unique(['dev_jam_id', 'user_id']);
        });

        Schema::create('dev_jam_creator_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['creator_user_id', 'blocked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_jam_creator_blocks');
        Schema::dropIfExists('dev_jam_outcome_votes');

        Schema::table('dev_jams', function (Blueprint $table) {
            $table->dropColumn(['is_suspended', 'suspended_at']);
        });
    }
};
