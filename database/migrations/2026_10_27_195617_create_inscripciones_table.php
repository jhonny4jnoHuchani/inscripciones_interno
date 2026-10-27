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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->enum('estado', ['En Curso', 'Aprobado', 'Reprobado'])->default('En Curso');
            $table->enum('tipo_registro', ['Auto-Inscripción', 'Kardex Manual'])->default('Auto-Inscripción');
            $table->timestamps();

            $table->unique(['estudiante_id', 'curso_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
