<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tribunals', function (Blueprint $table) {
            $table->string('sigle', 50)->nullable()->after('nom');
        });
    }

    public function down(): void
    {
        Schema::table('tribunals', function (Blueprint $table) {
            $table->dropColumn('sigle');
        });
    }
};