<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recours', function (Blueprint $table) {
            $table->id();

            // Références
            $table->string('numero_recours')->unique(); // Ex: REC/2025/001
            $table->foreignId('decision_id')->constrained('decisions');
            $table->foreignId('type_recours_id')->constrained('type_recours');
            $table->foreignId('annee_judiciaire_id')->constrained('annee_judiciaires');

            // Parties au recours
            $table->string('appelant')->nullable(); // Celui qui interjette le recours
            $table->string('intime')->nullable(); // La partie adverse

            // Dates importantes
            $table->date('date_decision_attaquee'); // Date de la décision attaquée
            $table->date('date_interjetee'); // Date d'interjection du recours
            $table->date('date_limite_recours'); // Date limite calculée
            $table->date('date_notification')->nullable(); // Date de notification

            // Recevabilité
            $table->enum('statut_recevabilite', [
                'en_cours_examen',
                'recevable',
                'irrecevable'
            ])->default('en_cours_examen');

            $table->text('motif_irrecevabilite')->nullable();
            $table->date('date_decision_recevabilite')->nullable();

            // Workflow (étape actuelle)
            $table->integer('etape_actuelle')->default(1); // De 1 à 11
            $table->enum('statut_global', [
                'en_cours',
                'cloture',
                'abandonne'
            ])->default('en_cours');

            // Observations
            $table->text('observations')->nullable();

            // Gestion
            $table->foreignId('greffier_responsable_id')->nullable()->constrained('users');
            $table->boolean('is_archived')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('numero_recours');
            $table->index('date_interjetee');
            $table->index('statut_recevabilite');
            $table->index('etape_actuelle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recours');
    }
};
