<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->onDelete('cascade');
            $table->foreignId('infraction_id')->constrained('infractions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['decision_id', 'infraction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_infractions');
    }
};
