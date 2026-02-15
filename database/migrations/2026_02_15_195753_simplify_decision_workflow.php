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
                'transmise_chef',
                'signee',
                'enregistree',
                'annulee',
                'archivee'
            ])->default('brouillon')->after('duree_peine');

            // Ajouter motif de transmission
            $table->text('motif_transmission')->nullable()->after('motif_annulation');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn('motif_transmission');
        });
    }
};
