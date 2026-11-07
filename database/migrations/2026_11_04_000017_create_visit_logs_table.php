<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('ip_address', 45)->index();
            $table->string('country')->default('France');
            $table->string('country_code', 6)->default('FR');
            $table->string('city')->nullable();
            $table->text('url');
            $table->string('route_name')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('duration_seconds')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
