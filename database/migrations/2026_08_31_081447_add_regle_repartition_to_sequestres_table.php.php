<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestres', function (Blueprint $table) {
            $table->enum('regle_repartition', [
                'succession_conjoint_enfants', // Décès parent, conjoint(s) + enfants : 1/4 - 3/4
                'succession_enfants_seuls',    // Pas de conjoint : parts égales entre enfants
                'separation_conjoints',        // Séparation : 50/50
                'personnalisee',               // La famille définit elle-même les parts
            ])->nullable()->after('taux_precompte');
        });
    }

    public function down(): void
    {
        Schema::table('sequestres', function (Blueprint $table) {
            $table->dropColumn('regle_repartition');
        });
    }
};
