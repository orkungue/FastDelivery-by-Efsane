<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        DB::statement("UPDATE users SET username = COALESCE(NULLIF(name, ''), SUBSTRING_INDEX(email, '@', 1)) WHERE username IS NULL");

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
    }
};