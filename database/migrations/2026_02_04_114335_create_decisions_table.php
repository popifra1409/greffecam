<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();

            // Références
            $table->string('numero_rg')->unique(); // Numéro au Registre Général
            $table->string('numero_parquet')->nullable();
            $table->foreignId('nature_decision_id')->constrained('nature_decisions');
            $table->foreignId('tribunal_id')->constrained('tribunals');

            // Dates
            $table->date('date_decision');
            $table->date('date_signature')->nullable();
            $table->date('date_factum')->nullable();
            $table->date('date_enregistrement')->nullable();

            // Composition du tribunal
            $table->string('president')->nullable();
            $table->string('juge_1')->nullable();
            $table->string('juge_2')->nullable();
            $table->string('greffier')->nullable();
            $table->string('ministere_public')->nullable();

            // Détails
            $table->text('resume')->nullable();
            $table->text('dispositif')->nullable();
            $table->decimal('montant_amende', 15, 2)->nullable();
            $table->string('duree_peine')->nullable(); // Ex: "2 ans"

            // Cycle de vie
            $table->enum('statut', [
                'brouillon',
                'en_attente_signature',
                'signee',
                'enregistree',
                'archivee'
            ])->default('brouillon');

            // Fichiers
            $table->string('fichier_scan')->nullable(); // Chemin vers le PDF scanné

            // Gestion
            $table->foreignId('greffier_responsable_id')->nullable()->constrained('users');
            $table->boolean('is_archived')->default(false);
            $table->date('date_archivage')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('numero_rg');
            $table->index('date_decision');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
