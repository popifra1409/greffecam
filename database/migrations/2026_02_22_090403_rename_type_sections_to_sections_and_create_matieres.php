<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supprimer d'abord les contraintes qui dépendent de la table sections
        if (Schema::hasTable('greffier_section')) {
            Schema::table('greffier_section', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
            });
            Schema::dropIfExists('greffier_section');
        }

        // 2. Renommer la table type_sections en sections_judiciaires (temporaire)
        if (Schema::hasTable('type_sections')) {
            Schema::rename('type_sections', 'sections_judiciaires');
        }

        // 3. Supprimer l'ancienne table sections
        if (Schema::hasTable('sections')) {
            Schema::dropIfExists('sections');
        }

        // 4. Renommer sections_judiciaires en sections
        if (Schema::hasTable('sections_judiciaires')) {
            Schema::rename('sections_judiciaires', 'sections');
        }

        // 5. Créer la table matieres si elle n'existe pas
        if (!Schema::hasTable('matieres')) {
            Schema::create('matieres', function (Blueprint $table) {
                $table->id();
                $table->foreignId('section_id')->constrained('sections');
                $table->string('designation');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['section_id', 'is_active']);
            });
        }

        // 6. Mettre à jour les références dans decisions (renommer type_section_id en section_id)
        if (Schema::hasColumn('decisions', 'type_section_id')) {
            DB::statement('ALTER TABLE decisions RENAME COLUMN type_section_id TO section_id');
        }

        // 7. Ajouter matiere_id dans decisions
        if (!Schema::hasColumn('decisions', 'matiere_id')) {
            Schema::table('decisions', function (Blueprint $table) {
                $table->foreignId('matiere_id')->nullable()->after('section_id')->constrained('matieres');
            });
        }

        // 8. Mettre à jour les références dans infractions
        if (Schema::hasColumn('infractions', 'type_section_id')) {
            DB::statement('ALTER TABLE infractions RENAME COLUMN type_section_id TO section_id');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('decisions', 'matiere_id')) {
            Schema::table('decisions', function (Blueprint $table) {
                $table->dropForeign(['matiere_id']);
                $table->dropColumn('matiere_id');
            });
        }

        if (Schema::hasTable('matieres')) {
            Schema::dropIfExists('matieres');
        }

        if (Schema::hasTable('sections')) {
            Schema::rename('sections', 'type_sections');
        }
    }
};
