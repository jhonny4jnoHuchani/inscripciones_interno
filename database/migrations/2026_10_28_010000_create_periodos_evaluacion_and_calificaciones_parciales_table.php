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
        Schema::create('periodos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->string('gestion', 20)->default('2026-I');
            $table->unsignedInteger('numero_parcial');
            $table->string('nombre', 100);
            $table->decimal('ponderacion', 5, 2)->default(33.33);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['gestion', 'numero_parcial']);
        });

        Schema::create('calificaciones_parciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->foreignId('periodo_evaluacion_id')->constrained('periodos_evaluacion')->cascadeOnDelete();
            $table->decimal('nota', 5, 2)->default(0);
            $table->string('observacion')->nullable();
            $table->timestamps();

            $table->unique(['inscripcion_id', 'periodo_evaluacion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones_parciales');
        Schema::dropIfExists('periodos_evaluacion');
    }
};
