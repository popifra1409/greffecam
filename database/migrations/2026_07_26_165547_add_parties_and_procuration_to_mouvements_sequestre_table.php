<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_sequestre', function (Blueprint $table) {
            // ✅ Versement : identifie la partie adverse (payeur) qui verse l'argent
            $table->foreignId('sequestre_partie_adverse_id')
                ->nullable()
                ->after('motif_mouvement_id')
                ->constrained('sequestre_parties_adverses')
                ->nullOnDelete();

            // ✅ Retrait : identifie l'ayant droit (bénéficiaire légal) concerné
            $table->foreignId('sequestre_ayant_droit_id')
                ->nullable()
                ->after('sequestre_partie_adverse_id')
                ->constrained('sequestre_ayants_droits')
                ->nullOnDelete();

            // ✅ Procuration : un tiers mandaté effectue le retrait à la place de l'ayant droit
            $table->boolean('est_procuration')->default(false)->after('sequestre_ayant_droit_id');
            $table->string('mandataire_nom')->nullable()->after('est_procuration');
            $table->string('mandataire_reference_procuration')->nullable()->after('mandataire_nom');

            // ✅ operateur_beneficiaire n'est plus saisi à la main : il devient
            // calculé automatiquement (nom de l'ayant droit/partie adverse, ou du
            // mandataire en cas de procuration). On le rend nullable côté schéma
            // par sécurité, même si le code le renseigne toujours.
            $table->string('operateur_beneficiaire')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_sequestre', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sequestre_partie_adverse_id');
            $table->dropConstrainedForeignId('sequestre_ayant_droit_id');
            $table->dropColumn(['est_procuration', 'mandataire_nom', 'mandataire_reference_procuration']);
            $table->string('operateur_beneficiaire')->nullable(false)->change();
        });
    }
};
