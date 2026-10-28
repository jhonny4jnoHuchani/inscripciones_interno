<?php

namespace Database\Seeders;

use App\Models\PeriodoEvaluacion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PeriodoEvaluacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gestion = '2026-I';

        // 1er Parcial (Habilitado actualmente para registro y pruebas)
        PeriodoEvaluacion::firstOrCreate(
            ['gestion' => $gestion, 'numero_parcial' => 1],
            [
                'nombre' => '1er Parcial (Ciclo 1)',
                'ponderacion' => 30.00,
                'fecha_inicio' => Carbon::now()->subDays(5),
                'fecha_fin' => Carbon::now()->addDays(10),
                'activo' => true,
            ]
        );

        // 2do Parcial (Próximo)
        PeriodoEvaluacion::firstOrCreate(
            ['gestion' => $gestion, 'numero_parcial' => 2],
            [
                'nombre' => '2do Parcial (Ciclo 2)',
                'ponderacion' => 30.00,
                'fecha_inicio' => Carbon::now()->addDays(20),
                'fecha_fin' => Carbon::now()->addDays(35),
                'activo' => true,
            ]
        );

        // 3er Parcial / Examen Final (Próximo)
        PeriodoEvaluacion::firstOrCreate(
            ['gestion' => $gestion, 'numero_parcial' => 3],
            [
                'nombre' => '3er Parcial / Evaluación Final (Ciclo 3)',
                'ponderacion' => 40.00,
                'fecha_inicio' => Carbon::now()->addDays(50),
                'fecha_fin' => Carbon::now()->addDays(65),
                'activo' => true,
            ]
        );
    }
}
