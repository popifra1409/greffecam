<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('juges', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('grade')->constrained('grades')->nullOnDelete();
        });

        // ✅ Migrer les valeurs texte existantes vers le référentiel Grade
        $gradesDistincts = DB::table('juges')
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->distinct()
            ->pluck('grade');

        foreach ($gradesDistincts as $libelle) {
            $gradeId = DB::table('grades')->insertGetId([
                'code' => \Illuminate\Support\Str::slug($libelle),
                'libelle' => $libelle,
                'type_grade' => 'juge',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('juges')->where('grade', $libelle)->update(['grade_id' => $gradeId]);
        }
    }

    public function down(): void
    {
        Schema::table('juges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grade_id');
        });
    }
};
