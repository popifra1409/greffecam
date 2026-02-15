<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Nouveau champ section
            $table->foreignId('section_id')->nullable()->after('tribunal_id')->constrained('sections');

            // N° répertoire (différent du RG)
            $table->string('numero_repertoire')->nullable()->after('numero_rg');

            // Date de saisie
            $table->timestamp('date_saisie')->nullable()->after('date_enregistrement');

            // Montant dépens
            $table->decimal('montant_depens', 15, 2)->nullable()->after('montant_amende');

            // Nature de la décision (contradictoire, par défaut, avant dit droit)
            $table->enum('nature_rendu', [
                'contradictoire',
                'par_defaut',
                'avant_dit_droit'
            ])->nullable()->after('nature_decision_id');

            // Modifier le statut pour ajouter "saisie"
            $table->dropColumn('statut');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->enum('statut', [
                'brouillon',
                'saisie',
                'en_attente_signature',
                'signee',
                'enregistree',
                'archivee'
            ])->default('brouillon')->after('duree_peine');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn([
                'section_id',
                'numero_repertoire',
                'date_saisie',
                'montant_depens',
                'nature_rendu',
            ]);
        });
    }
};
