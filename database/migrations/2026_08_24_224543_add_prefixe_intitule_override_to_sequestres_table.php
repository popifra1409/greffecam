<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestres', function (Blueprint $table) {
            $table->string('prefixe_intitule_override')->nullable()->after('nom_intitule');
        });
    }

    public function down(): void
    {
        Schema::table('sequestres', function (Blueprint $table) {
            $table->dropColumn('prefixe_intitule_override');
        });
    }
};
