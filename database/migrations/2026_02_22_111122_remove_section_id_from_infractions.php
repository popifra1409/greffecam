<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('infractions', 'section_id')) {
            // Récupérer le nom de la contrainte
            $foreignKeys = DB::select("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = 'infractions' 
                AND constraint_type = 'FOREIGN KEY'
                AND constraint_name LIKE '%section_id%'
            ");

            // Supprimer la contrainte si elle existe
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE infractions DROP CONSTRAINT IF EXISTS {$fk->constraint_name}");
            }

            // Supprimer la colonne
            Schema::table('infractions', function (Blueprint $table) {
                $table->dropColumn('section_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained('sections');
        });
    }
};
