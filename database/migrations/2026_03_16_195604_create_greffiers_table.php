<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('greffiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribunal_id')->constrained('tribunals');

            // Informations personnelles
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('titre')->nullable(); // M., Mme
            $table->string('grade')->nullable(); // Greffier en chef, Greffier principal, etc.

            // Contact
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();

            // Fonction spécifique
            $table->boolean('est_chef')->default(false); // Greffier en chef

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tribunal_id', 'is_active']);
        });

        // Table pivot pour l'affectation des greffiers aux sections
        Schema::create('greffier_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('greffier_id')->constrained('greffiers')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['greffier_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('greffier_section');
        Schema::dropIfExists('greffiers');
    }
};
