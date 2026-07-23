<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequestre_ayants_droits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequestre_id')->constrained('sequestres')->cascadeOnDelete();
            $table->string('nom_complet');
            $table->string('numero_cni')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequestre_ayants_droits');
    }
};
