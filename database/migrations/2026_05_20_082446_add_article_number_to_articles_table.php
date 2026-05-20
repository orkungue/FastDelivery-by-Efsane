<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('articles', 'article_number')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->string('article_number')->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('articles', 'article_number')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('article_number');
            });
        }
    }
};