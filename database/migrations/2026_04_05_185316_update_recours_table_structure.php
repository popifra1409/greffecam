<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recours', function (Blueprint $table) {
            // ✅ Ajouter les nouvelles colonnes SEULEMENT si elles n'existent pas
            if (!Schema::hasColumn('recours', 'numero_recours')) {
                $table->string('numero_recours')->nullable()->after('id');
            }
            if (!Schema::hasColumn('recours', 'type_recours')) {
                $table->string('type_recours')->nullable()->after('numero_recours');
            }
            if (!Schema::hasColumn('recours', 'date_recours')) {
                $table->date('date_recours')->nullable()->after('type_recours');
            }
            if (!Schema::hasColumn('recours', 'reference_lettre')) {
                $table->string('reference_lettre')->nullable()->after('date_recours');
            }
            if (!Schema::hasColumn('recours', 'fichier_lettre')) {
                $table->string('fichier_lettre')->nullable()->after('reference_lettre');
            }
            if (!Schema::hasColumn('recours', 'date_enregistrement')) {
                $table->date('date_enregistrement')->nullable()->after('fichier_lettre');
            }
            if (!Schema::hasColumn('recours', 'date_transmission_cour_appel')) {
                $table->date('date_transmission_cour_appel')->nullable()->after('date_enregistrement');
            }
            if (!Schema::hasColumn('recours', 'documents_mise_en_etat')) {
                $table->json('documents_mise_en_etat')->nullable()->after('date_transmission_cour_appel');
            }
        });

        // ✅ Supprimer les colonnes SEULEMENT si elles existent
        Schema::table('recours', function (Blueprint $table) {
            $columnsToRemove = [
                'type_recours_id',
                'annee_judiciaire_id',
                'appelant',
                'intime',
                'date_decision_attaquee',
                'date_interjetee',
                'date_limite_recours',
                'date_notification',
                'statut_recevabilite',
                'motif_irrecevabilite',
                'date_decision_recevabilite',
                'etape_actuelle',
                'statut_global',
                'observations',
                'greffier_responsable_id',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('recours', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('recours', function (Blueprint $table) {
            // Restaurer les anciennes colonnes si rollback
            if (!Schema::hasColumn('recours', 'type_recours_id')) {
                $table->foreignId('type_recours_id')->nullable()->constrained('type_recours');
            }
            if (!Schema::hasColumn('recours', 'annee_judiciaire_id')) {
                $table->foreignId('annee_judiciaire_id')->nullable()->constrained('annees_judiciaires');
            }
            if (!Schema::hasColumn('recours', 'appelant')) {
                $table->string('appelant')->nullable();
            }
            if (!Schema::hasColumn('recours', 'intime')) {
                $table->string('intime')->nullable();
            }
            if (!Schema::hasColumn('recours', 'date_decision_attaquee')) {
                $table->date('date_decision_attaquee')->nullable();
            }
            if (!Schema::hasColumn('recours', 'date_interjetee')) {
                $table->date('date_interjetee')->nullable();
            }
            if (!Schema::hasColumn('recours', 'date_limite_recours')) {
                $table->date('date_limite_recours')->nullable();
            }
            if (!Schema::hasColumn('recours', 'date_notification')) {
                $table->date('date_notification')->nullable();
            }
            if (!Schema::hasColumn('recours', 'statut_recevabilite')) {
                $table->enum('statut_recevabilite', ['en_cours_examen', 'recevable', 'irrecevable'])->default('en_cours_examen');
            }
            if (!Schema::hasColumn('recours', 'etape_actuelle')) {
                $table->integer('etape_actuelle')->default(1);
            }
            if (!Schema::hasColumn('recours', 'statut_global')) {
                $table->enum('statut_global', ['en_cours', 'cloture', 'abandonne'])->default('en_cours');
            }
        });

        // Supprimer les nouvelles colonnes
        Schema::table('recours', function (Blueprint $table) {
            $newColumns = [
                'numero_recours',
                'type_recours',
                'date_recours',
                'reference_lettre',
                'fichier_lettre',
                'date_enregistrement',
                'date_transmission_cour_appel',
                'documents_mise_en_etat',
            ];

            foreach ($newColumns as $column) {
                if (Schema::hasColumn('recours', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};