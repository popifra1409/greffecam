<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestre_parties_adverses', function (Blueprint $table) {
            $table->date('date_debut_paiement')->nullable()->after('adresse');
            $table->enum('periodicite', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])->default('mensuel')->after('date_debut_paiement');
            $table->unsignedInteger('duree_contrat_mois')->nullable()->after('periodicite');
        });

        // ✅ Migrer les données existantes : jour_echeance -> date_debut_paiement
        // (on prend le jour du mois en cours comme point de départ, à ajuster si besoin)
        DB::table('sequestre_parties_adverses')
            ->whereNotNull('jour_echeance')
            ->orderBy('id')
            ->get(['id', 'jour_echeance', 'montant_loyer_attendu'])
            ->each(function ($ligne) {
                DB::table('sequestre_parties_adverses')
                    ->where('id', $ligne->id)
                    ->update([
                        'date_debut_paiement' => now()->startOfMonth()->addDays($ligne->jour_echeance - 1),
                    ]);
            });

        Schema::table('sequestre_parties_adverses', function (Blueprint $table) {
            $table->renameColumn('montant_loyer_attendu', 'montant_echeance');
            $table->dropColumn('jour_echeance');
        });
    }

    public function down(): void
    {
        Schema::table('sequestre_parties_adverses', function (Blueprint $table) {
            $table->renameColumn('montant_echeance', 'montant_loyer_attendu');
            $table->unsignedTinyInteger('jour_echeance')->nullable();
            $table->dropColumn(['date_debut_paiement', 'periodicite', 'duree_contrat_mois']);
        });
    }
};
