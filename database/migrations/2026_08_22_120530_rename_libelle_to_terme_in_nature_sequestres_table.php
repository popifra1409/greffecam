<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->renameColumn('libelle_ayants_droit', 'terme_ayants_droit');
            $table->renameColumn('libelle_parties_adverses', 'terme_parties_adverses');
        });
    }

    public function down(): void
    {
        Schema::table('nature_sequestres', function (Blueprint $table) {
            $table->renameColumn('terme_ayants_droit', 'libelle_ayants_droit');
            $table->renameColumn('terme_parties_adverses', 'libelle_parties_adverses');
        });
    }
};
