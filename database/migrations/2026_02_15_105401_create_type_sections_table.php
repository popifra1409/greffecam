<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_sections', function (Blueprint $table) {
            $table->id();
            $table->string('libelle'); // Ex: Civil, Commercial, Correctionnel
            $table->string('code', 20)->unique(); // Ex: CIV, COMM, CORR
            $table->text('description')->nullable();

            // Configuration des types de parties selon la section
            $table->json('types_parties')->nullable(); // Stocke les types de parties autorisés

            // Configuration de la composition du tribunal
            $table->boolean('utilise_assesseur')->default(false); // Pour TDL

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_sections');
    }
};
