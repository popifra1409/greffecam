<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('alerte_recours');
    }

    public function down(): void
    {
        // Si besoin de rollback, recréer la table
        Schema::create('alerte_recours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->cascadeOnDelete();
            $table->string('type_alerte');
            $table->string('niveau');
            $table->text('message');
            $table->date('date_limite')->nullable();
            $table->integer('jours_restants')->nullable();
            $table->boolean('est_lue')->default(false);
            $table->timestamp('date_lecture')->nullable();
            $table->foreignId('lu_par')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
};