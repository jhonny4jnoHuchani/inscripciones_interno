<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materia_requisito', function (Blueprint $table) {
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('requisito_id')->constrained('materias')->cascadeOnDelete();
            $table->primary(['materia_id', 'requisito_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_requisito');
    }
};
