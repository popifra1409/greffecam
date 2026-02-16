<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transmission_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->onDelete('cascade');

            // Qui transmet
            $table->foreignId('expediteur_id')->constrained('users');

            // À qui
            $table->foreignId('destinataire_id')->constrained('users');

            // Motif de la transmission
            $table->enum('motif', [
                'validation',
                'signature',
                'correction',
                'avis',
                'information',
                'autre'
            ]);

            // Statut de la transmission
            $table->enum('statut', [
                'en_attente',      // En attente de traitement
                'acceptee',        // Acceptée et traitée
                'rejetee',         // Rejetée
                'retournee'        // Retournée à l'expéditeur
            ])->default('en_attente');

            // Observations
            $table->text('observations_expediteur')->nullable();
            $table->text('observations_destinataire')->nullable();

            // Dates
            $table->timestamp('date_transmission');
            $table->timestamp('date_traitement')->nullable();

            $table->timestamps();

            $table->index(['decision_id', 'statut']);
            $table->index('destinataire_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transmission_decisions');
    }
};
