<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('content_views')) {
            Schema::create('content_views', function (Blueprint $table) {
                $table->id();
                $table->string('viewable_type', 50)->index(); // 'article', 'game_review', 'app_review'
                $table->unsignedBigInteger('viewable_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index(); // Author/Creator ID
                $table->string('ip_address', 45)->index();
                $table->string('user_agent', 500)->nullable();
                $table->string('device_fingerprint', 64)->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->date('viewed_date')->index();
                $table->timestamps();

                $table->index(['viewable_type', 'viewable_id', 'device_fingerprint'], 'unique_content_view_idx');
                $table->index(['admin_id', 'created_at'], 'admin_views_timeline_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
