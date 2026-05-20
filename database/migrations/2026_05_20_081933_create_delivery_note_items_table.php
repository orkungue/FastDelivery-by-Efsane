<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_note_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('article_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};