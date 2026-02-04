<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infractions', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('categorie')->nullable(); // Crime, Délit, Contravention
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infractions');
    }
};
