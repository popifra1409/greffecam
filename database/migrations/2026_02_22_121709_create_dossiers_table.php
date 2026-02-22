<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();

            // Hiérarchie
            $table->foreignId('tribunal_id')->constrained('tribunals');
            $table->foreignId('section_id')->constrained('sections');
            $table->foreignId('matiere_id')->constrained('matieres');
            $table->foreignId('annee_judiciaire_id')->constrained('annee_judiciaires');

            // Numéro unique du dossier
            $table->string('numero_dossier')->unique();

            // Demandeur (personne qui enrôle)
            $table->boolean('demandeur_est_personne_morale')->default(false);

            // Personne physique
            $table->string('demandeur_nom')->nullable();
            $table->string('demandeur_prenom')->nullable();
            $table->date('demandeur_date_naissance')->nullable();
            $table->string('demandeur_lieu_naissance')->nullable();
            $table->string('demandeur_profession')->nullable();
            $table->string('demandeur_nationalite')->nullable()->default('Camerounaise');

            // Personne morale
            $table->string('demandeur_raison_sociale')->nullable();
            $table->string('demandeur_representant_legal')->nullable();

            // Contact
            $table->text('demandeur_adresse')->nullable();
            $table->string('demandeur_telephone')->nullable();
            $table->string('demandeur_email')->nullable();

            // Avocat du demandeur
            $table->string('avocat_demandeur_nom')->nullable();
            $table->string('avocat_demandeur_contact')->nullable();

            // Dates
            $table->date('date_enrolement');
            $table->date('date_cloture')->nullable();

            // Statut
            $table->enum('statut', [
                'ouvert',
                'en_instance',      // Décision rendue, en attente de grosse
                'grosse_delivree',  // Grosse délivrée
                'en_recours',       // Recours en cours
                'clos'              // Définitivement clos
            ])->default('ouvert');

            $table->text('observations')->nullable();

            // Greffier qui a enrôlé
            $table->foreignId('enrole_par')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('numero_dossier');
            $table->index(['tribunal_id', 'section_id', 'matiere_id']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
