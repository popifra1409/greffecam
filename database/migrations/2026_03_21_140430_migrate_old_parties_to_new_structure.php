<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Dossier;
use App\Models\DossierPartie;

return new class extends Migration
{
    public function up(): void
    {
        // Migrer les anciennes parties vers la nouvelle structure
        Dossier::whereNotNull('demandeur_nom')->chunk(100, function ($dossiers) {
            foreach ($dossiers as $dossier) {
                // Migrer le demandeur
                if ($dossier->demandeur_nom || $dossier->demandeur_raison_sociale) {
                    DossierPartie::create([
                        'dossier_id' => $dossier->id,
                        'type_partie' => 'demandeur',
                        'est_personne_morale' => $dossier->demandeur_est_personne_morale,
                        'nom' => $dossier->demandeur_nom,
                        'prenom' => $dossier->demandeur_prenom,
                        'date_naissance' => $dossier->demandeur_date_naissance,
                        'lieu_naissance' => $dossier->demandeur_lieu_naissance,
                        'profession' => $dossier->demandeur_profession,
                        'nationalite' => $dossier->demandeur_nationalite,
                        'raison_sociale' => $dossier->demandeur_raison_sociale,
                        'representant_legal' => $dossier->demandeur_representant_legal,
                        'adresse' => $dossier->demandeur_adresse,
                        'telephone' => $dossier->demandeur_telephone,
                        'email' => $dossier->demandeur_email,
                        'avocat_nom' => $dossier->avocat_demandeur_nom,
                        'avocat_contact' => $dossier->avocat_demandeur_contact,
                    ]);
                }

                // Migrer le défendeur
                if ($dossier->defendeur_nom || $dossier->defendeur_raison_sociale) {
                    DossierPartie::create([
                        'dossier_id' => $dossier->id,
                        'type_partie' => 'defendeur',
                        'est_personne_morale' => $dossier->defendeur_est_personne_morale,
                        'nom' => $dossier->defendeur_nom,
                        'prenom' => $dossier->defendeur_prenom,
                        'date_naissance' => $dossier->defendeur_date_naissance,
                        'lieu_naissance' => $dossier->defendeur_lieu_naissance,
                        'profession' => $dossier->defendeur_profession,
                        'nationalite' => $dossier->defendeur_nationalite,
                        'raison_sociale' => $dossier->defendeur_raison_sociale,
                        'representant_legal' => $dossier->defendeur_representant_legal,
                        'adresse' => $dossier->defendeur_adresse,
                        'telephone' => $dossier->defendeur_telephone,
                        'email' => $dossier->defendeur_email,
                        'avocat_nom' => $dossier->avocat_defendeur_nom,
                        'avocat_contact' => $dossier->avocat_defendeur_contact,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Pas de rollback nécessaire
    }
};
