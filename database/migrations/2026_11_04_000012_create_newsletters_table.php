<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('subject_fr');
            $table->string('subject_en')->nullable();
            $table->longText('content_fr');
            $table->longText('content_en')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
