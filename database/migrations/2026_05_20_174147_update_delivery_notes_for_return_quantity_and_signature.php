<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->decimal('return_quantity', 10, 2)->default(0)->after('quantity');
        });

        DB::statement('UPDATE delivery_note_items SET return_quantity = quantity WHERE `return` = 1');

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn('return');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->text('customer_signature')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->boolean('return')->default(false)->after('quantity');
        });

        DB::statement('UPDATE delivery_note_items SET `return` = 1 WHERE return_quantity > 0');

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn('return_quantity');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('customer_signature');
        });
    }
};