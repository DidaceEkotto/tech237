<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dev_jams', function (Blueprint $table) {
            $table->unsignedInteger('hearts_count')->default(0)->after('allow_external_links');
        });
    }

    public function down(): void
    {
        Schema::table('dev_jams', function (Blueprint $table) {
            $table->dropColumn('hearts_count');
        });
    }
};
