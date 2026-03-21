<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter le numéro personnalisé aux dossiers
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('numero_dossier_personnalise')->nullable()->after('numero_dossier');
        });

        // 2. Créer la table des parties du dossier
        Schema::create('dossier_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');

            // Type de partie (demandeur, defendeur, partie_civile, prevenu, temoin)
            $table->enum('type_partie', ['demandeur', 'defendeur', 'partie_civile', 'prevenu', 'temoin']);

            // Personne physique ou morale
            $table->boolean('est_personne_morale')->default(false);

            // Identité - Personne physique
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('profession')->nullable();
            $table->string('nationalite')->nullable()->default('Camerounaise');

            // Identité - Personne morale
            $table->string('raison_sociale')->nullable();
            $table->string('representant_legal')->nullable();

            // Contact
            $table->text('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();

            // Avocat
            $table->string('avocat_nom')->nullable();
            $table->string('avocat_contact')->nullable();

            $table->timestamps();

            $table->index(['dossier_id', 'type_partie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_parties');

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('numero_dossier_personnalise');
        });
    }
};
