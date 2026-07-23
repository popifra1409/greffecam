<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequestre_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequestre_id')->constrained('sequestres')->cascadeOnDelete();
            $table->enum('categorie', ['courrier', 'procedure', 'contrat', 'quittance']);
            $table->string('libelle');
            $table->string('fichier_path'); // chemin sur le disque privé
            $table->string('fichier_nom_original')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('depose_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sequestre_id', 'categorie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequestre_documents');
    }
};
