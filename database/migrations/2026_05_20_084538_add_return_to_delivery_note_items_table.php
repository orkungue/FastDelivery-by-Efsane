<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->boolean('return')->default(false)->after('quantity');
            $table->dropColumn(['unit', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->text('description')->nullable()->after('unit');
            $table->dropColumn('return');
        });
    }
};