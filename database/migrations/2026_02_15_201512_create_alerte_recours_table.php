<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerte_recours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recours_id')->constrained('recours')->onDelete('cascade');

            // Type d'alerte
            $table->enum('niveau', [
                'jaune',  // J-15
                'orange', // J-7
                'rouge'   // H-48
            ]);

            $table->string('titre');
            $table->text('message');

            // Dates
            $table->timestamp('date_declenchement');
            $table->timestamp('date_lecture')->nullable();

            // Destinataires
            $table->json('destinataires_ids'); // IDs des users à alerter

            $table->boolean('est_lue')->default(false);
            $table->boolean('est_envoyee')->default(false);

            $table->timestamps();

            $table->index(['recours_id', 'niveau']);
            $table->index('date_declenchement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerte_recours');
    }
};
