<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequestre_parties_tierces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequestre_id')->constrained('sequestres')->cascadeOnDelete();
            $table->enum('type_partie_tierce', ['huissier', 'avocat', 'service_public', 'autre'])->default('autre');
            $table->string('nom_complet');
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('reference')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequestre_parties_tierces');
    }
};
