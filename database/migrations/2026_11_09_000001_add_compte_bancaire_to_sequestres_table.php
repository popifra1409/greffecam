<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestres', function (Blueprint $table) {
            $table->string('numero_compte_bancaire')->nullable()->after('date_ouverture');
            $table->date('date_ouverture_compte')->nullable()->after('numero_compte_bancaire');
        });

        Schema::create('banque_releves', function (Blueprint $table) {
            $table->id();

            // Un relevé = la situation d'un compte, à une date d'arrêté donnée.
            $table->foreignId('sequestre_id')->constrained('sequestres')->cascadeOnDelete();

            $table->date('date_releve');
            $table->decimal('solde_banque', 15, 2); // solde fourni par la banque
            $table->text('observations')->nullable();

            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Un seul relevé par séquestre et par date d'arrêté.
            $table->unique(['sequestre_id', 'date_releve']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banque_releves');

        Schema::table('sequestres', function (Blueprint $table) {
            $table->dropColumn(['numero_compte_bancaire', 'date_ouverture_compte']);
        });
    }
};
