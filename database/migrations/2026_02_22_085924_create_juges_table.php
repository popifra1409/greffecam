<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('juges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribunal_id')->constrained('tribunals');

            // Informations personnelles
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('titre')->nullable(); // M., Mme, Me
            $table->string('grade')->nullable(); // Magistrat 1er grade, 2e grade, etc.

            // Contact
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tribunal_id', 'is_active']);
        });

        // Ajouter aussi les greffiers au tribunal
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tribunal_id')->nullable()->after('email')->constrained('tribunals');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tribunal_id']);
            $table->dropColumn('tribunal_id');
        });

        Schema::dropIfExists('juges');
    }
};
