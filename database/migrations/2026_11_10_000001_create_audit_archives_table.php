<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_archives', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier')->unique();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->unsignedInteger('nombre_entrees');
            $table->unsignedBigInteger('taille_octets');
            $table->string('chemin');
            $table->timestamps();

            $table->index(['periode_debut', 'periode_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_archives');
    }
};
