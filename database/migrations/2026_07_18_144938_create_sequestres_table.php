<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequestres', function (Blueprint $table) {
            $table->id();

            // ✅ Le séquestre découle d'une décision déjà rendue dans ce dossier
            $table->foreignId('decision_id')->constrained('decisions')->cascadeOnDelete();

            // ✅ Dénormalisé depuis decision->dossier_id : permet un accès direct
            // Dossier::sequestres() sans passer par hasManyThrough
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();

            // ✅ Représentant de la famille = une des parties déjà enrôlées dans ce dossier
            $table->foreignId('dossier_partie_id')->nullable()->constrained('dossier_parties')->nullOnDelete();

            $table->foreignId('nature_sequestre_id')->constrained('nature_sequestres');
            $table->foreignId('statut_sequestre_id')->constrained('statut_sequestres');

            $table->date('date_ouverture');
            $table->decimal('taux_precompte', 6, 4); // ex: 0.0500 = 5%
            $table->text('observations')->nullable();

            $table->timestamps();

            $table->index(['dossier_id', 'decision_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequestres');
    }
};
