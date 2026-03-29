<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('module_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('module_code'); // decision_recours, sequestre_caution, documents_judiciaires
            $table->boolean('can_access')->default(true);
            $table->timestamps();

            $table->unique(['role_id', 'module_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_accesses');
    }
};