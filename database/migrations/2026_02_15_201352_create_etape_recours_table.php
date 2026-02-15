<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etape_recours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recours_id')->constrained('recours')->onDelete('cascade');

            // Numéro de l'étape (1 à 11)
            $table->integer('numero_etape');
            $table->string('libelle'); // Ex: "Dépôt du recours", "Transmission au greffe"

            // Statut de l'étape
            $table->enum('statut', [
                'en_attente',
                'en_cours',
                'completee',
                'bloquee'
            ])->default('en_attente');

            // Dates
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_completion')->nullable();
            $table->date('date_limite')->nullable(); // Si un délai s'applique

            // Détails
            $table->text('description')->nullable();
            $table->text('observations')->nullable();

            // Utilisateur qui a complété l'étape
            $table->foreignId('completee_par')->nullable()->constrained('users');

            // Documents générés à cette étape
            $table->json('documents_generes')->nullable();

            $table->timestamps();

            $table->unique(['recours_id', 'numero_etape']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etape_recours');
    }
};
