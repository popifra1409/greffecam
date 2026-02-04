<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jour_feries', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date');
            $table->boolean('is_recurrent')->default(false); // Si c'est tous les ans (ex: Fête du travail)
            $table->integer('annee');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['date', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jour_feries');
    }
};
