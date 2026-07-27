<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->string('libelle_ayants_droit')->nullable()->after('description');
            $table->string('libelle_parties_adverses')->nullable()->after('libelle_ayants_droit');
        });

        // ✅ Valeurs par défaut suggérées pour les natures déjà existantes
        $defauts = [
            'sequestre' => ['Ayants droit', 'Locataires (versants)'],
            'succession' => ['Héritiers', 'Débiteurs de la succession'],
            'administration' => ['Bénéficiaires', 'Contributeurs'],
            'tutelle' => ['Ayants droit', 'Débirentiers'],
            'curatelle' => ['Bénéficiaire', 'Débirentiers'],
        ];

        foreach ($defauts as $code => [$libelleAyantsDroit, $libellePartiesAdverses]) {
            DB::table('nature_sequestres')
                ->where('code', $code)
                ->update([
                    'libelle_ayants_droit' => $libelleAyantsDroit,
                    'libelle_parties_adverses' => $libellePartiesAdverses,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->dropColumn(['libelle_ayants_droit', 'libelle_parties_adverses']);
        });
    }
};
