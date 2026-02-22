<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter toutes les colonnes manquantes
        Schema::table('matieres', function (Blueprint $table) {
            if (!Schema::hasColumn('matieres', 'section_id')) {
                $table->foreignId('section_id')->after('id')->constrained('sections');
            }

            if (!Schema::hasColumn('matieres', 'designation')) {
                $table->string('designation')->after('section_id');
            }

            if (!Schema::hasColumn('matieres', 'description')) {
                $table->text('description')->nullable()->after('designation');
            }

            if (!Schema::hasColumn('matieres', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        // Ajouter l'index
        Schema::table('matieres', function (Blueprint $table) {
            $table->index(['section_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('matieres', function (Blueprint $table) {
            $table->dropIndex(['section_id', 'is_active']);
            $table->dropForeign(['section_id']);
            $table->dropColumn(['section_id', 'designation', 'description', 'is_active']);
        });
    }
};
