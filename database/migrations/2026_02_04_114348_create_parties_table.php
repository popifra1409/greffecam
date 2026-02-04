<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->onDelete('cascade');

            $table->enum('type', [
                'prevenu',
                'victime',
                'partie_civile',
                'temoin'
            ]);

            // Personne physique
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('profession')->nullable();
            $table->string('nationalite')->nullable();

            // Contact
            $table->text('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();

            // Personne morale (si applicable)
            $table->boolean('is_personne_morale')->default(false);
            $table->string('raison_sociale')->nullable();
            $table->string('representant_legal')->nullable();

            // Avocat
            $table->string('avocat_nom')->nullable();
            $table->string('avocat_contact')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
