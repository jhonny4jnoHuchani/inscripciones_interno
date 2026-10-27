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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ci', 30)->nullable()->unique()->after('email');
            $table->decimal('nota_admision', 5, 2)->nullable()->after('carrera_id');
            $table->string('modalidad_admision')->default('Examen de Admisión')->after('nota_admision');
            $table->string('estado_admision')->default('Pendiente')->after('modalidad_admision');
            $table->date('fecha_admision')->nullable()->after('estado_admision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ci',
                'nota_admision',
                'modalidad_admision',
                'estado_admision',
                'fecha_admision',
            ]);
        });
    }
};
