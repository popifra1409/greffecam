<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recours', function (Blueprint $table) {
            // Ajouter type_recours_id pour la relation
            if (!Schema::hasColumn('recours', 'type_recours_id')) {
                $table->foreignId('type_recours_id')
                    ->nullable()
                    ->after('type_recours')
                    ->constrained('type_recours')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('recours', function (Blueprint $table) {
            if (Schema::hasColumn('recours', 'type_recours_id')) {
                $table->dropForeign(['type_recours_id']);
                $table->dropColumn('type_recours_id');
            }
        });
    }
};