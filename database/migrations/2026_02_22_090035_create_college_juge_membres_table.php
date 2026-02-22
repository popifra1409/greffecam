<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('college_juge_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_juge_id')->constrained('college_juges')->onDelete('cascade');
            $table->foreignId('juge_id')->constrained('juges')->onDelete('cascade');

            // Qualité du juge dans ce collège
            $table->enum('qualite', [
                'president',
                'juge_1',
                'juge_2',
                'assesseur_1',
                'assesseur_2',
                'juge_suppléant'
            ]);

            $table->timestamps();

            $table->unique(['college_juge_id', 'juge_id', 'qualite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('college_juge_membres');
    }
};
