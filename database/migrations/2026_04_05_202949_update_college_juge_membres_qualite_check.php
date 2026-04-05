<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Supprimer l'ancienne contrainte
        DB::statement('ALTER TABLE college_juge_membres DROP CONSTRAINT IF EXISTS college_juge_membres_qualite_check');

        // Ajouter la nouvelle contrainte avec les nouvelles valeurs
        DB::statement("ALTER TABLE college_juge_membres ADD CONSTRAINT college_juge_membres_qualite_check CHECK (qualite IN ('president', 'membre', 'assesseur', 'juge_1', 'juge_2', 'assesseur_1', 'assesseur_2', 'juge_suppleant'))");
    }

    public function down(): void
    {
        // Restaurer l'ancienne contrainte
        DB::statement('ALTER TABLE college_juge_membres DROP CONSTRAINT IF EXISTS college_juge_membres_qualite_check');

        DB::statement("ALTER TABLE college_juge_membres ADD CONSTRAINT college_juge_membres_qualite_check CHECK (qualite IN ('president', 'juge_1', 'juge_2', 'assesseur_1', 'assesseur_2', 'juge_suppleant'))");
    }
};