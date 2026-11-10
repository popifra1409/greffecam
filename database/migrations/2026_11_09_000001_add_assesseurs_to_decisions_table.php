<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->string('assesseur_1')->nullable()->after('juge_unique_id');
            $table->string('assesseur_2')->nullable()->after('assesseur_1');
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn(['assesseur_1', 'assesseur_2']);
        });
    }
};
