<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_sites', function (Blueprint $table) {
            if (!Schema::hasColumn('portfolio_sites', 'site_type')) {
                $table->string('site_type', 20)->default('onepage')->after('slug');
            }
            if (!Schema::hasColumn('portfolio_sites', 'domain')) {
                $table->string('domain')->nullable()->unique()->after('site_type');
            }
            if (!Schema::hasColumn('portfolio_sites', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('is_published');
            }
            if (!Schema::hasColumn('portfolio_sites', 'unique_visitors_count')) {
                $table->unsignedBigInteger('unique_visitors_count')->default(0)->after('views_count');
            }
        });

        Schema::create('portfolio_site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_site_id')->constrained('portfolio_sites')->cascadeOnDelete();
            $table->string('page_slug', 120)->default('accueil');
            $table->string('visitor_hash', 64);
            $table->string('referer', 255)->nullable();
            $table->date('visited_date');
            $table->timestamps();

            $table->index(['portfolio_site_id', 'visited_date']);
            $table->index(['portfolio_site_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_site_visits');
        Schema::table('portfolio_sites', function (Blueprint $table) {
            $table->dropColumn(['site_type', 'domain', 'views_count', 'unique_visitors_count']);
        });
    }
};
