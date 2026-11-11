<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeat_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('uuid', 36)->unique();
            $table->string('name', 150);
            $table->string('url', 500);
            $table->integer('check_interval_minutes')->default(5); // 1, 5, 10, 15, 30, 60
            $table->integer('expected_status_code')->default(200);
            $table->string('http_method', 10)->default('GET');
            $table->integer('timeout_seconds')->default(10);
            $table->string('status', 20)->default('pending'); // up, down, degraded, pending, paused
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->string('email_recipient', 150)->nullable();
            
            // Metrics & telemetry
            $table->integer('last_status_code')->nullable();
            $table->integer('last_response_time_ms')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->timestamp('last_down_at')->nullable();
            $table->timestamp('last_up_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->decimal('uptime_percentage', 5, 2)->default(100.00);
            $table->integer('total_checks')->default(0);
            $table->integer('successful_checks')->default(0);
            $table->integer('failed_checks')->default(0);
            $table->boolean('is_currently_down')->default(false);
            $table->timestamp('down_alert_sent_at')->nullable();
            
            // Public status sharing
            $table->boolean('is_public')->default(false);

            $table->timestamps();
        });

        Schema::create('heartbeat_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('heartbeat_monitor_id')->constrained('heartbeat_monitors')->cascadeOnDelete();
            $table->integer('status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->boolean('is_up')->default(true);
            $table->string('error_message', 500)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['heartbeat_monitor_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeat_checks');
        Schema::dropIfExists('heartbeat_monitors');
    }
};
