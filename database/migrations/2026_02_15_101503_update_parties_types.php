<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->enum('type', [
                // Pour Civil, Commercial, Social
                'demandeur',
                'defendeur',
                // Pour Correctionnel et TDL
                'ministere_public',
                'partie_civile',
                'prevenu',
                // Commun
                'temoin'
            ])->after('decision_id');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->enum('type', [
                'prevenu',
                'victime',
                'partie_civile',
                'temoin'
            ])->after('decision_id');
        });
    }
};
