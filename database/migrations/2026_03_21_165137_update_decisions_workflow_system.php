<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // ✅ RETIRER numero_rg
            if (Schema::hasColumn('decisions', 'numero_rg')) {
                $table->dropColumn('numero_rg');
            }

            // ✅ AJOUTER date_premiere_audience
            $table->date('date_premiere_audience')->nullable()->after('date_decision');

            // ✅ DATES DE WORKFLOW
            $table->date('date_modification')->nullable()->after('date_saisie');

            // ✅ FICHIERS À CHAQUE ÉTAPE
            $table->string('fichier_saisi')->nullable()->after('fichier_scan');
            $table->string('fichier_saisi_modifie')->nullable()->after('fichier_saisi');
            $table->string('fichier_signe')->nullable()->after('fichier_saisi_modifie');
            $table->string('fichier_enregistre')->nullable()->after('fichier_signe');

            // ✅ ENREGISTREMENT (Volume, Folio, Case BD, Quittance)
            $table->string('numero_volume')->nullable()->after('numero_parquet');
            $table->string('numero_folio')->nullable()->after('numero_volume');
            $table->string('numero_case_bd')->nullable()->after('numero_folio');
            $table->string('numero_quittance')->nullable()->after('numero_case_bd');
            $table->decimal('montant_quittance', 15, 2)->nullable()->after('numero_quittance');

            // ✅ CERTIFICAT DE NON-APPEL ET GROSSE
            $table->string('certificat_non_appel_reference')->nullable();
            $table->date('certificat_non_appel_date')->nullable();
            $table->string('certificat_non_appel_fichier')->nullable();

            $table->string('grosse_reference')->nullable();
            $table->date('grosse_date')->nullable();
            $table->string('grosse_fichier')->nullable();

            // ✅ OPPOSITION
            $table->boolean('a_opposition')->default(false);
            $table->string('lettre_opposition_reference')->nullable();
            $table->date('lettre_opposition_date')->nullable();
            $table->string('lettre_opposition_fichier')->nullable();

            // ✅ MODIFIER STATUTS
            // Les statuts seront : brouillon, validee, saisie, signee, enregistree, archivee
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn([
                'date_premiere_audience',
                'date_modification',
                'fichier_saisi',
                'fichier_saisi_modifie',
                'fichier_signe',
                'fichier_enregistre',
                'numero_volume',
                'numero_folio',
                'numero_case_bd',
                'numero_quittance',
                'montant_quittance',
                'certificat_non_appel_reference',
                'certificat_non_appel_date',
                'certificat_non_appel_fichier',
                'grosse_reference',
                'grosse_date',
                'grosse_fichier',
                'a_opposition',
                'lettre_opposition_reference',
                'lettre_opposition_date',
                'lettre_opposition_fichier',
            ]);
        });
    }
};
