<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('greffier_id')
                ->nullable()
                ->after('tribunal_id')
                ->constrained('greffiers')
                ->nullOnDelete();

            $table->index('greffier_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['greffier_id']);
            $table->dropIndex(['greffier_id']);
            $table->dropColumn('greffier_id');
        });
    }
};
