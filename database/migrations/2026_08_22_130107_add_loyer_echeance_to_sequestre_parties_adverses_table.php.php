<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequestre_parties_adverses', function (Blueprint $table) {
            $table->decimal('montant_loyer_attendu', 15, 2)->nullable()->after('adresse');
            $table->unsignedTinyInteger('jour_echeance')->nullable()->after('montant_loyer_attendu');
        });
    }

    public function down(): void
    {
        Schema::table('sequestre_parties_adverses', function (Blueprint $table) {
            $table->dropColumn(['montant_loyer_attendu', 'jour_echeance']);
        });
    }
};
