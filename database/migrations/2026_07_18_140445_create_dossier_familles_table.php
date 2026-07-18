<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_familles', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier_famille')->unique(); // Format: SEQ/2026/000001

            $table->foreignId('decision_id')->constrained('decisions')->cascadeOnDelete();
            $table->foreignId('dossier_partie_id')->nullable()->constrained('dossier_parties')->nullOnDelete();
            $table->foreignId('nature_sequestre_id')->constrained('nature_sequestres');
            $table->foreignId('statut_sequestre_id')->constrained('statut_sequestres');

            $table->string('intitule_dossier'); // Nom de famille / partie
            $table->date('date_ouverture');
            $table->decimal('taux_precompte', 6, 4); // ex: 0.0500 = 5%
            $table->text('observations')->nullable();

            $table->timestamps();

            $table->index('intitule_dossier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_familles');
    }
};
