<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Utilisateur qui détient actuellement la décision
            $table->foreignId('detenteur_actuel_id')->nullable()->after('greffier_responsable_id')->constrained('users');

            // Modifier les statuts pour inclure "rejetee"
            $table->dropColumn('statut');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->enum('statut', [
                'brouillon',
                'transmise_chef',
                'rejetee',
                'signee',
                'enregistree',
                'annulee',
                'archivee'
            ])->default('brouillon')->after('duree_peine');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['detenteur_actuel_id']);
            $table->dropColumn('detenteur_actuel_id');
        });
    }
};
