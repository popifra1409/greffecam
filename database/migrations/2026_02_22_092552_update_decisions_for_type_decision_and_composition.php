<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Ajouter type_decision_id
            $table->foreignId('type_decision_id')->nullable()->after('nature_decision_id')->constrained('type_decisions');

            // Ajouter un champ pour déterminer si c'est un juge unique ou un collège
            $table->enum('mode_composition', ['juge_unique', 'college'])->default('juge_unique')->after('college_juge_id');

            // Ajouter juge_unique_id pour les décisions rendues par un juge seul
            $table->foreignId('juge_unique_id')->nullable()->after('mode_composition')->constrained('juges');

            // Les anciens champs president, juge_1, juge_2, assesseur deviennent obsolètes
            // mais on les garde pour la compatibilité ou pour les cas particuliers
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['type_decision_id']);
            $table->dropColumn(['type_decision_id', 'mode_composition', 'juge_unique_id']);
        });
    }
};
