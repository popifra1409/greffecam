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
            $table->foreignId('dossier_famille_id')->constrained('dossier_familles')->cascadeOnDelete();
            $table->foreignId('motif_mouvement_id')->nullable()->constrained('motifs_mouvements')->nullOnDelete();

            $table->date('date_mouvement');
            $table->string('operateur_beneficiaire'); // "Désignation" dans l'Excel
            $table->enum('type_mouvement', ['versement', 'retrait']);

            $table->decimal('montant_mouvement', 15, 2); // Montant brut saisi
            $table->decimal('taux_applique', 6, 4)->nullable(); // Taux du dossier au moment du mouvement
            $table->decimal('montant_precompte', 15, 2)->default(0); // = montant × taux (versement uniquement)
            $table->decimal('montant_net', 15, 2); // Impact réel sur le solde
            $table->decimal('solde_apres', 15, 2); // Solde cumulé après ce mouvement (ledger)

            $table->timestamps();

            $table->index(['dossier_famille_id', 'date_mouvement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_sequestre');
    }
};
