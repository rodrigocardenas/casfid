<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->unsignedInteger('pokemon_id');
            $table->string('pokemon_name');
            $table->string('pokemon_type');

            $table->timestamps();

            // Índices para optimización y constraint de unicidad
            $table->index('user_id');
            $table->index('pokemon_id');
            $table->unique(['user_id', 'pokemon_id'], 'uq_user_pokemon_favorite');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
