<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles') && !Schema::hasColumn('articles', 'rubrique_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->foreignId('rubrique_id')->nullable()->after('admin_id')->constrained('rubriques')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'rubrique_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('rubrique_id');
            });
        }
    }
};
