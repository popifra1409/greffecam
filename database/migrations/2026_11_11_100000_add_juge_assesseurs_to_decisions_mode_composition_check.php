<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Le formulaire Décision propose « Juge + assesseurs » pour les
        // sections de droit local, mais la contrainte d'origine (issue de
        // l'enum) ne connaissait que juge_unique et college : toute saisie
        // d'assesseurs échouait en base.
        DB::statement('ALTER TABLE decisions DROP CONSTRAINT IF EXISTS decisions_mode_composition_check');

        DB::statement("
            ALTER TABLE decisions
            ADD CONSTRAINT decisions_mode_composition_check
            CHECK (mode_composition IN ('juge_unique', 'juge_assesseurs', 'college'))
        ");
    }

    public function down(): void
    {
        // La restauration échouera volontairement si des décisions
        // « juge_assesseurs » existent encore : il faut d'abord les requalifier
        // explicitement, jamais les convertir en silence.
        DB::statement('ALTER TABLE decisions DROP CONSTRAINT IF EXISTS decisions_mode_composition_check');

        DB::statement("
            ALTER TABLE decisions
            ADD CONSTRAINT decisions_mode_composition_check
            CHECK (mode_composition IN ('juge_unique', 'college'))
        ");
    }
};
