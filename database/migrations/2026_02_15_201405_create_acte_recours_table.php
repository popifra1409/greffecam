<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acte_recours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recours_id')->constrained('recours')->onDelete('cascade');
            $table->foreignId('etape_recours_id')->nullable()->constrained('etape_recours');

            // Type d'acte
            $table->enum('type_acte', [
                'pv_depot',
                'notification',
                'convocation',
                'ar', // Accusé de réception
                'ordonnance',
                'autre'
            ]);

            $table->string('numero_acte')->unique();
            $table->string('libelle');
            $table->text('contenu')->nullable();

            // Fichier généré
            $table->string('fichier_path')->nullable();

            // Dates
            $table->date('date_generation');
            $table->date('date_envoi')->nullable();
            $table->date('date_reception')->nullable(); // Pour les AR

            // Destinataire
            $table->string('destinataire')->nullable();
            $table->text('adresse_destinataire')->nullable();

            // Généré par
            $table->foreignId('genere_par')->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acte_recours');
    }
};
