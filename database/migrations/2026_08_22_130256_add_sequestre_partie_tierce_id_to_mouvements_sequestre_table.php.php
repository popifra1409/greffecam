<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_sequestre', function (Blueprint $table) {
            $table->foreignId('sequestre_partie_tierce_id')
                ->nullable()
                ->after('sequestre_ayant_droit_id')
                ->constrained('sequestre_parties_tierces')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_sequestre', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sequestre_partie_tierce_id');
        });
    }
};
