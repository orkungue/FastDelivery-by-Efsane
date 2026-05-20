<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE delivery_notes MODIFY customer_signature MEDIUMTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE delivery_notes MODIFY customer_signature TEXT NULL');
    }
};