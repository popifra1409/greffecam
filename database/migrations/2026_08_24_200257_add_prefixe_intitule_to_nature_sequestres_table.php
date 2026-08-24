<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->string('prefixe_intitule')->nullable()->after('terme_partie_tierce');
        });
    }

    public function down(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->dropColumn('prefixe_intitule');
        });
    }
};
