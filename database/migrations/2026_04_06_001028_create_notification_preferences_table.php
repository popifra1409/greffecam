<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Canaux activés
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('push_enabled')->default(true); // Notifications base de données

            // Contacts
            $table->string('phone_number')->nullable();
            $table->string('whatsapp_number')->nullable();

            // Fréquence
            $table->enum('frequence', ['quotidien', 'bi_quotidien', 'hebdomadaire'])->default('quotidien');

            // Heures d'envoi (pour ne pas déranger en dehors des heures de travail)
            $table->time('heure_debut')->default('08:00:00');
            $table->time('heure_fin')->default('18:00:00');

            // Types d'alertes à recevoir
            $table->boolean('recours_non_enregistres')->default(true); // >3j
            $table->boolean('recours_non_transmis')->default(true); // >7j
            $table->boolean('recours_urgents')->default(true); // >30j

            // Résumé quotidien
            $table->boolean('resume_quotidien')->default(true);
            $table->time('heure_resume')->default('08:00:00');

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};