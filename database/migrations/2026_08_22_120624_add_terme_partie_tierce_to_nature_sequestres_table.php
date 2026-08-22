<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->string('terme_partie_tierce')->nullable()->after('terme_parties_adverses');
        });

        $defauts = [
            'sequestre' => 'Prestataires (Huissier, Avocat, Services)',
            'succession' => 'Notaires & Prestataires',
            'administration' => 'Prestataires & Services publics',
            'tutelle' => 'Prestataires & Services publics',
            'curatelle' => 'Prestataires & Services publics',
        ];

        foreach ($defauts as $code => $terme) {
            DB::table('nature_sequestres')->where('code', $code)->update(['terme_partie_tierce' => $terme]);
        }
    }

    public function down(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->dropColumn('terme_partie_tierce');
        });
    }
};
