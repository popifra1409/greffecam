<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : Ajouter la nouvelle colonne comme nullable
        Schema::table('decisions', function (Blueprint $table) {
            $table->foreignId('type_section_id')->nullable()->after('tribunal_id');
        });

        // Étape 2 : Migrer les données existantes (récupérer type_section_id depuis la section)
        DB::statement('
            UPDATE decisions 
            SET type_section_id = (
                SELECT type_section_id 
                FROM sections 
                WHERE sections.id = decisions.section_id
            )
            WHERE section_id IS NOT NULL
        ');

        // Étape 3 : Rendre la colonne NOT NULL et ajouter la contrainte
        Schema::table('decisions', function (Blueprint $table) {
            $table->foreignId('type_section_id')->nullable(false)->change();
            $table->foreign('type_section_id')->references('id')->on('type_sections');
        });

        // Étape 4 : Supprimer l'ancienne colonne
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }

    public function down(): void
    {
        // Recréer section_id
        Schema::table('decisions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('tribunal_id')->constrained('sections');
        });

        // Supprimer type_section_id
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['type_section_id']);
            $table->dropColumn('type_section_id');
        });
    }
};
