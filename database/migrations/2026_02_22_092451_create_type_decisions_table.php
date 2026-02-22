<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_decision_id')->constrained('categorie_decisions');
            $table->string('libelle'); // Ex: Jugement au fond, Jugement avant dire droit, etc.
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['categorie_decision_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_decisions');
    }
};
