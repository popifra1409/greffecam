<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            $table->foreignId('infraction_id')->constrained('infractions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['dossier_id', 'infraction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_infractions');
    }
};
