<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // ✅ HIÉRARCHIE : Vérifier avant d'ajouter
            if (!Schema::hasColumn('decisions', 'categorie_decision_id')) {
                $table->foreignId('categorie_decision_id')
                    ->nullable()
                    ->after('dossier_id')
                    ->constrained('categorie_decisions')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('decisions', 'type_decision_id')) {
                $table->foreignId('type_decision_id')
                    ->nullable()
                    ->after('categorie_decision_id')
                    ->constrained('type_decisions')
                    ->nullOnDelete();
            }

            // ✅ SIGNIFICATION
            if (!Schema::hasColumn('decisions', 'est_signifiee')) {
                $table->boolean('est_signifiee')
                    ->default(false)
                    ->after('date_enregistrement');
            }

            if (!Schema::hasColumn('decisions', 'date_signification')) {
                $table->date('date_signification')
                    ->nullable()
                    ->after('est_signifiee');
            }

            if (!Schema::hasColumn('decisions', 'reference_acte_huissier')) {
                $table->string('reference_acte_huissier')
                    ->nullable()
                    ->after('date_signification');
            }

            if (!Schema::hasColumn('decisions', 'fichier_signification')) {
                $table->string('fichier_signification')
                    ->nullable()
                    ->after('reference_acte_huissier');
            }

            // ✅ TYPE DE RECOURS
            if (!Schema::hasColumn('decisions', 'type_recours')) {
                $table->enum('type_recours', ['appel', 'opposition'])
                    ->nullable()
                    ->after('fichier_signification');
            }

            // ✅ APPEL
            if (!Schema::hasColumn('decisions', 'lettre_appel_reference')) {
                $table->string('lettre_appel_reference')
                    ->nullable()
                    ->after('type_recours');
            }

            if (!Schema::hasColumn('decisions', 'lettre_appel_date')) {
                $table->date('lettre_appel_date')
                    ->nullable()
                    ->after('lettre_appel_reference');
            }

            if (!Schema::hasColumn('decisions', 'lettre_appel_fichier')) {
                $table->string('lettre_appel_fichier')
                    ->nullable()
                    ->after('lettre_appel_date');
            }

            // ✅ OPPOSITION (vérifier si existe déjà)
            if (!Schema::hasColumn('decisions', 'lettre_opposition_reference')) {
                $table->string('lettre_opposition_reference')
                    ->nullable()
                    ->after('lettre_appel_fichier');
            }

            if (!Schema::hasColumn('decisions', 'lettre_opposition_date')) {
                $table->date('lettre_opposition_date')
                    ->nullable()
                    ->after('lettre_opposition_reference');
            }

            if (!Schema::hasColumn('decisions', 'lettre_opposition_fichier')) {
                $table->string('lettre_opposition_fichier')
                    ->nullable()
                    ->after('lettre_opposition_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Supprimer les foreign keys si elles existent
            if (Schema::hasColumn('decisions', 'categorie_decision_id')) {
                $table->dropForeign(['categorie_decision_id']);
                $table->dropColumn('categorie_decision_id');
            }

            if (Schema::hasColumn('decisions', 'type_decision_id')) {
                $table->dropForeign(['type_decision_id']);
                $table->dropColumn('type_decision_id');
            }

            // Supprimer les autres colonnes si elles existent
            $columnsToRemove = [
                'est_signifiee',
                'date_signification',
                'reference_acte_huissier',
                'fichier_signification',
                'type_recours',
                'lettre_appel_reference',
                'lettre_appel_date',
                'lettre_appel_fichier',
                'lettre_opposition_reference',
                'lettre_opposition_date',
                'lettre_opposition_fichier',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('decisions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};