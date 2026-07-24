<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('greffiers', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('grade')->constrained('grades')->nullOnDelete();
        });

        $gradesDistincts = DB::table('greffiers')
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->distinct()
            ->pluck('grade');

        foreach ($gradesDistincts as $libelle) {
            // Réutiliser un grade existant du même libellé (créé par la migration précédente) si présent
            $existant = DB::table('grades')->where('libelle', $libelle)->first();

            if ($existant) {
                $gradeId = $existant->id;
                // S'il ne servait qu'aux juges, l'ouvrir aussi aux greffiers
                if ($existant->type_grade === 'juge') {
                    DB::table('grades')->where('id', $gradeId)->update(['type_grade' => 'les_deux']);
                }
            } else {
                $gradeId = DB::table('grades')->insertGetId([
                    'code' => \Illuminate\Support\Str::slug($libelle),
                    'libelle' => $libelle,
                    'type_grade' => 'greffier',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('greffiers')->where('grade', $libelle)->update(['grade_id' => $gradeId]);
        }
    }

    public function down(): void
    {
        Schema::table('greffiers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grade_id');
        });
    }
};
