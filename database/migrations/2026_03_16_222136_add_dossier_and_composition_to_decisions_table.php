<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Lien avec le dossier (vérifier si n'existe pas déjà)
            if (!Schema::hasColumn('decisions', 'dossier_id')) {
                $table->foreignId('dossier_id')->nullable()->after('id')->constrained('dossiers')->nullOnDelete();
            }

            // Composition du tribunal
            if (!Schema::hasColumn('decisions', 'mode_composition')) {
                $table->enum('mode_composition', ['juge_unique', 'college'])->default('juge_unique')->after('section_id');
            }

            if (!Schema::hasColumn('decisions', 'juge_unique_id')) {
                $table->foreignId('juge_unique_id')->nullable()->after('mode_composition')->constrained('juges')->nullOnDelete();
            }

            if (!Schema::hasColumn('decisions', 'college_juge_id')) {
                $table->foreignId('college_juge_id')->nullable()->after('juge_unique_id')->constrained('college_juges')->nullOnDelete();
            }

            if (!Schema::hasColumn('decisions', 'greffier_id')) {
                $table->foreignId('greffier_id')->nullable()->after('college_juge_id')->constrained('greffiers')->nullOnDelete();
            }

            // Matière (si n'existe pas)
            if (!Schema::hasColumn('decisions', 'matiere_id')) {
                $table->foreignId('matiere_id')->nullable()->after('section_id')->constrained('matieres')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            if (Schema::hasColumn('decisions', 'dossier_id')) {
                $table->dropForeign(['dossier_id']);
                $table->dropColumn('dossier_id');
            }

            if (Schema::hasColumn('decisions', 'juge_unique_id')) {
                $table->dropForeign(['juge_unique_id']);
                $table->dropColumn('juge_unique_id');
            }

            if (Schema::hasColumn('decisions', 'college_juge_id')) {
                $table->dropForeign(['college_juge_id']);
                $table->dropColumn('college_juge_id');
            }

            if (Schema::hasColumn('decisions', 'greffier_id')) {
                $table->dropForeign(['greffier_id']);
                $table->dropColumn('greffier_id');
            }

            if (Schema::hasColumn('decisions', 'matiere_id')) {
                $table->dropForeign(['matiere_id']);
                $table->dropColumn('matiere_id');
            }

            if (Schema::hasColumn('decisions', 'mode_composition')) {
                $table->dropColumn('mode_composition');
            }
        });
    }
};
