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
        Schema::table('materias', function (Blueprint $table) {
            $table->foreignId('carrera_id')->nullable()->after('id')->constrained('carreras')->cascadeOnDelete();
            $table->unsignedInteger('nivel')->default(1)->after('nota_minima_aprobacion');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('carrera_id')->nullable()->after('email')->constrained('carreras')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn(['carrera_id', 'nivel']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn(['carrera_id']);
        });
    }
};
