<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'is_in_person')) {
                $table->boolean('is_in_person')->default(true)->after('location');
            }
            if (!Schema::hasColumn('conferences', 'is_free')) {
                $table->boolean('is_free')->default(true)->after('is_in_person');
            }
            if (!Schema::hasColumn('conferences', 'price')) {
                $table->string('price')->nullable()->after('is_free');
            }
            if (!Schema::hasColumn('conferences', 'website_url')) {
                $table->string('website_url')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn(['is_in_person', 'is_free', 'price', 'website_url']);
        });
    }
};
