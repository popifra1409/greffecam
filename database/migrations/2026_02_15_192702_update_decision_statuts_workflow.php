<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn('statut');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->enum('statut', [
                'brouillon',
                'validee',
                'transmise_chef',
                'signee',
                'enregistree',
                'annulee',
                'archivee'
            ])->default('brouillon')->after('duree_peine');

            $table->text('motif_annulation')->nullable()->after('statut');
            $table->timestamp('date_validation')->nullable()->after('date_saisie');
            $table->foreignId('validee_par')->nullable()->constrained('users')->after('greffier_responsable_id');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['validee_par']);
            $table->dropColumn(['motif_annulation', 'date_validation', 'validee_par']);
        });
    }
};
