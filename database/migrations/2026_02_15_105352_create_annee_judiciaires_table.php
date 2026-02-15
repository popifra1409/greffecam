<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annee_judiciaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle'); // Ex: "2024-2025"
            $table->date('date_debut'); // Ex: 01/10/2024
            $table->date('date_fin'); // Ex: 30/09/2025
            $table->boolean('is_active')->default(false); // Une seule année active
            $table->boolean('is_cloturee')->default(false);
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annee_judiciaires');
    }
};
