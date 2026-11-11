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
        // 1. Table des devis & factures freelance
        Schema::create('freelance_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('quote_number', 50);
            $table->string('type', 20)->default('quote'); // quote, invoice
            $table->string('status', 30)->default('draft'); // draft, sent, accepted, rejected, paid
            $table->string('title', 255);
            $table->string('client_name', 150);
            $table->string('client_company', 150)->nullable();
            $table->string('client_email', 150)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->string('client_address', 255)->nullable();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 10)->default('XAF');
            $table->json('items')->nullable();
            $table->bigInteger('subtotal')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->bigInteger('total_amount')->default(0);
            $table->json('payment_milestones')->nullable();
            $table->json('payment_methods')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['uuid', 'status']);
        });

        // 2. Table des paramètres et simulations de TJM du freelance
        Schema::create('freelance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('target_monthly_net')->default(400000);
            $table->integer('work_weeks_per_year')->default(46);
            $table->integer('work_days_per_week')->default(5);
            $table->integer('non_billable_percent')->default(25);
            $table->decimal('tax_percent', 5, 2)->default(5.5);
            $table->json('expenses')->nullable();
            $table->bigInteger('computed_tjm')->default(40000);
            $table->bigInteger('computed_thm')->default(5500);
            $table->json('payment_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelance_settings');
        Schema::dropIfExists('freelance_quotes');
    }
};
