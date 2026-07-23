<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_parties', function (Blueprint $table) {
            $table->boolean('est_famille')->default(false)->after('est_personne_morale');
            $table->string('nom_famille')->nullable()->after('est_famille');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_parties', function (Blueprint $table) {
            $table->dropColumn(['est_famille', 'nom_famille']);
        });
    }
};
