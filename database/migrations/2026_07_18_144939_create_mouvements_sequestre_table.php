<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_sequestre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequestre_id')->constrained('sequestres')->cascadeOnDelete();
            $table->foreignId('motif_mouvement_id')->nullable()->constrained('motifs_mouvements')->nullOnDelete();

            $table->date('date_mouvement');
            $table->string('operateur_beneficiaire');
            $table->enum('type_mouvement', ['versement', 'retrait']);

            $table->decimal('montant_mouvement', 15, 2);
            $table->decimal('taux_applique', 6, 4)->nullable();
            $table->decimal('montant_precompte', 15, 2)->default(0);
            $table->decimal('montant_net', 15, 2);
            $table->decimal('solde_apres', 15, 2);

            $table->timestamps();

            $table->index(['sequestre_id', 'date_mouvement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_sequestre');
    }
};
