<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Ajouter dossier_id
            $table->foreignId('dossier_id')->nullable()->after('annee_judiciaire_id')->constrained('dossiers');

            // Les champs tribunal_id, section_id, matiere_id deviennent optionnels
            // car ils seront hérités du dossier
            $table->foreignId('tribunal_id')->nullable()->change();
            $table->foreignId('section_id')->nullable()->change();
            $table->foreignId('matiere_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['dossier_id']);
            $table->dropColumn('dossier_id');
        });
    }
};
