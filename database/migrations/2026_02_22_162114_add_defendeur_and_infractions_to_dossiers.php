<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Date d'assignation
            $table->date('date_assignation')->nullable()->after('date_enrolement');

            // Défendeur - Identité
            $table->boolean('defendeur_est_personne_morale')->default(false)->after('avocat_demandeur_contact');

            // Personne physique
            $table->string('defendeur_nom')->nullable()->after('defendeur_est_personne_morale');
            $table->string('defendeur_prenom')->nullable()->after('defendeur_nom');
            $table->date('defendeur_date_naissance')->nullable()->after('defendeur_prenom');
            $table->string('defendeur_lieu_naissance')->nullable()->after('defendeur_date_naissance');
            $table->string('defendeur_profession')->nullable()->after('defendeur_lieu_naissance');
            $table->string('defendeur_nationalite')->nullable()->default('Camerounaise')->after('defendeur_profession');

            // Personne morale
            $table->string('defendeur_raison_sociale')->nullable()->after('defendeur_nationalite');
            $table->string('defendeur_representant_legal')->nullable()->after('defendeur_raison_sociale');

            // Contact
            $table->text('defendeur_adresse')->nullable()->after('defendeur_representant_legal');
            $table->string('defendeur_telephone')->nullable()->after('defendeur_adresse');
            $table->string('defendeur_email')->nullable()->after('defendeur_telephone');

            // Avocat du défendeur
            $table->string('avocat_defendeur_nom')->nullable()->after('defendeur_email');
            $table->string('avocat_defendeur_contact')->nullable()->after('avocat_defendeur_nom');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'date_assignation',
                'defendeur_est_personne_morale',
                'defendeur_nom',
                'defendeur_prenom',
                'defendeur_date_naissance',
                'defendeur_lieu_naissance',
                'defendeur_profession',
                'defendeur_nationalite',
                'defendeur_raison_sociale',
                'defendeur_representant_legal',
                'defendeur_adresse',
                'defendeur_telephone',
                'defendeur_email',
                'avocat_defendeur_nom',
                'avocat_defendeur_contact',
            ]);
        });
    }
};
