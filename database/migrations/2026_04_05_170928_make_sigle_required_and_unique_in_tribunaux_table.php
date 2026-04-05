<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tribunals', function (Blueprint $table) {
            // Modifier la colonne pour la rendre obligatoire et unique
            $table->string('sigle', 50)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tribunals', function (Blueprint $table) {
            $table->dropUnique(['sigle']);
            $table->string('sigle', 50)->nullable()->change();
        });
    }
};