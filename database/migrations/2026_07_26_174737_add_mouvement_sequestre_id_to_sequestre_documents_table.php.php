<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestre_documents', function (Blueprint $table) {
            $table->foreignId('mouvement_sequestre_id')
                ->nullable()
                ->after('sequestre_id')
                ->constrained('mouvements_sequestre')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sequestre_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mouvement_sequestre_id');
        });
    }
};
