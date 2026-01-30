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
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->integer('pokedex_id')->unique();
            $table->string('name')->unique();
            $table->string('type')->nullable();
            $table->integer('hp')->default(0);
            $table->integer('attack')->default(0);
            $table->integer('defense')->default(0);
            $table->integer('sp_attack')->default(0);
            $table->integer('sp_defense')->default(0);
            $table->integer('speed')->default(0);
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
