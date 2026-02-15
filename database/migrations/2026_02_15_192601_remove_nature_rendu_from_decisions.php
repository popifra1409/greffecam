<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn('nature_rendu');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->enum('nature_rendu', [
                'contradictoire',
                'par_defaut',
                'avant_dit_droit'
            ])->nullable()->after('nature_decision_id');
        });
    }
};
