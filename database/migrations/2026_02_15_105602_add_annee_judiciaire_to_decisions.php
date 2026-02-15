<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->foreignId('annee_judiciaire_id')->after('section_id')->constrained('annee_judiciaires');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['annee_judiciaire_id']);
            $table->dropColumn('annee_judiciaire_id');
        });
    }
};
