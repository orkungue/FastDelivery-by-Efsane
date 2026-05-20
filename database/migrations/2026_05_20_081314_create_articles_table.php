<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->string('article_number')->unique();
            $table->string('name');
            $table->string('unit')->default('Stück');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};