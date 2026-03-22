<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ SUPPRIMER L'ANCIENNE CONTRAINTE
        DB::statement('ALTER TABLE decisions DROP CONSTRAINT IF EXISTS decisions_statut_check');

        // ✅ AJOUTER LA NOUVELLE CONTRAINTE AVEC TOUS LES STATUTS
        DB::statement("
            ALTER TABLE decisions 
            ADD CONSTRAINT decisions_statut_check 
            CHECK (statut IN (
                'brouillon',
                'validee',
                'saisie',
                'signee',
                'enregistree',
                'archivee',
                'transmise_chef',
                'annulee'
            ))
        ");
    }

    public function down(): void
    {
        // ✅ RESTAURER L'ANCIENNE CONTRAINTE
        DB::statement('ALTER TABLE decisions DROP CONSTRAINT IF EXISTS decisions_statut_check');

        DB::statement("
            ALTER TABLE decisions 
            ADD CONSTRAINT decisions_statut_check 
            CHECK (statut IN (
                'brouillon',
                'transmise',
                'signee',
                'enregistree',
                'annulee',
                'archivee'
            ))
        ");
    }
};
