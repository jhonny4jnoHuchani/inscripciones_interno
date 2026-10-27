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
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('paralelo', 10)->default('A')->after('gestion');
            $table->unsignedInteger('cupo_maximo')->default(50)->after('paralelo');
            $table->foreignId('docente_id')->nullable()->change();
            $table->dateTime('fecha_inicio_calificacion')->nullable()->change();
            $table->dateTime('fecha_fin_calificacion')->nullable()->change();

            $table->unique(['materia_id', 'gestion', 'paralelo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropUnique(['materia_id', 'gestion', 'paralelo']);
            $table->dropColumn(['paralelo', 'cupo_maximo']);
        });
    }
};
