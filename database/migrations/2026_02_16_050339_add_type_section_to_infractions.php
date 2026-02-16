<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            $table->foreignId('type_section_id')->nullable()->after('code')->constrained('type_sections');
        });
    }

    public function down(): void
    {
        Schema::table('infractions', function (Blueprint $table) {
            $table->dropForeign(['type_section_id']);
            $table->dropColumn('type_section_id');
        });
    }
};
