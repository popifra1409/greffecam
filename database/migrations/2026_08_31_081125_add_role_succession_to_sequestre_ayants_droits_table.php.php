<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestre_ayants_droits', function (Blueprint $table) {
            $table->enum('role_succession', ['conjoint', 'enfant', 'autre'])->nullable()->after('adresse');
            $table->decimal('pourcentage_manuel', 5, 2)->nullable()->after('role_succession');
        });
    }

    public function down(): void
    {
        Schema::table('sequestre_ayants_droits', function (Blueprint $table) {
            $table->dropColumn(['role_succession', 'pourcentage_manuel']);
        });
    }
};
