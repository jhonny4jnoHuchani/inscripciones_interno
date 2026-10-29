<?php

namespace Database\Seeders;

use App\Models\CalificacionParcial;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Garantiza datos completos, realistas y listos para presentación:
     * - Docente Demo (docente1@academico.test) con 3 cursos, alumnos y notas.
     * - Estudiante Demo (estudiante1@academico.test) con 4 materias activas y 10 materias aprobadas en historial.
     * - Cursos activos con estudiantes y calificaciones en 1er Parcial.
     * - 1 curso vacante sin docente para demostración en vivo de asignación.
     */
    public function run(): void
    {
        $carreraSistemas = Carrera::where('codigo', 'SIS')->first() ?? Carrera::first();
        $materias = Materia::all()->keyBy('sigla');
        $periodos = PeriodoEvaluacion::where('gestion', '2026-I')->orderBy('numero_parcial')->get();
        $primerParcial = $periodos->firstWhere('numero_parcial', 1);
        $segundoParcial = $periodos->firstWhere('numero_parcial', 2);

        // ==============================================================
        // 1. DOCENTE DEMO: docente1@academico.test (Lic. Roberto Gómez)
        // ==============================================================
        $docente1 = User::firstOrCreate(
            ['email' => 'docente1@academico.test'],
            [
                'name' => 'Lic. Roberto Gómez Soliz (Docente Demo)',
                'ci' => '20009999',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $docente1->syncRoles(['Docente']);

        // Asignar 3 cursos específicos de 2026-I a docente1
        $siglasDocente1 = ['MAT-101', 'MAT-102', 'ALG-101'];
        foreach ($siglasDocente1 as $sigla) {
            if ($materias->has($sigla)) {
                Curso::updateOrCreate(
                    [
                        'materia_id' => $materias[$sigla]->id,
                        'gestion' => '2026-I',
                        'paralelo' => $sigla === 'ALG-101' ? 'B' : 'A',
                    ],
                    [
                        'docente_id' => $docente1->id,
                        'cupo_maximo' => 45,
                        'fecha_inicio_calificacion' => now()->subDays(5),
                        'fecha_fin_calificacion' => now()->addDays(30),
                    ]
                );
            }
        }

        // ==============================================================
        // 2. ESTUDIANTE DEMO: estudiante1@academico.test (Juan Pérez Gómez)
        // ==============================================================
        $estudiante1 = User::firstOrCreate(
            ['email' => 'estudiante1@academico.test'],
            [
                'name' => 'Juan Pérez Gómez (Estudiante Demo)',
                'ci' => '30009999',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
                'modalidad_admision' => 'Examen de Admisión',
                'nota_admision' => 88.50,
                'estado_admision' => 'Admitido',
                'fecha_admision' => now()->subYears(1),
            ]
        );
        $estudiante1->syncRoles(['Estudiante']);

        // Limpiar inscripciones previas de estudiante1 para evitar duplicidad
        $estudiante1->inscripciones()->delete();

        // A) HISTORIAL APROBADO: 10 materias de Nivel 1 y Nivel 2 (2025-I y 2025-II)
        $materiasAprobadas = [
            ['sigla' => 'MAT-101', 'nota' => 84.0, 'gestion' => '2025-I'],
            ['sigla' => 'SIS-101', 'nota' => 95.0, 'gestion' => '2025-I'],
            ['sigla' => 'FIS-101', 'nota' => 76.0, 'gestion' => '2025-I'],
            ['sigla' => 'ALG-101', 'nota' => 88.0, 'gestion' => '2025-I'],
            ['sigla' => 'LIN-101', 'nota' => 92.0, 'gestion' => '2025-I'],
            ['sigla' => 'MAT-102', 'nota' => 80.0, 'gestion' => '2025-II'],
            ['sigla' => 'SIS-102', 'nota' => 94.0, 'gestion' => '2025-II'],
            ['sigla' => 'FIS-102', 'nota' => 78.0, 'gestion' => '2025-II'],
            ['sigla' => 'MAT-103', 'nota' => 86.0, 'gestion' => '2025-II'],
            ['sigla' => 'LIN-102', 'nota' => 96.0, 'gestion' => '2025-II'],
        ];

        foreach ($materiasAprobadas as $m) {
            if ($materias->has($m['sigla'])) {
                $cHist = Curso::firstOrCreate(
                    [
                        'materia_id' => $materias[$m['sigla']]->id,
                        'gestion' => $m['gestion'],
                        'paralelo' => 'A',
                    ],
                    [
                        'cupo_maximo' => 45,
                        'docente_id' => $docente1->id,
                        'fecha_inicio_calificacion' => now()->subMonths(6),
                        'fecha_fin_calificacion' => now()->subMonths(4),
                    ]
                );

                Inscripcion::create([
                    'estudiante_id' => $estudiante1->id,
                    'curso_id' => $cHist->id,
                    'nota_final' => $m['nota'],
                    'estado' => 'Aprobado',
                    'tipo_registro' => 'Auto-Inscripción',
                ]);
            }
        }

        // B) MATERIAS ACTIVAS 2026-I (Nivel 3)
        $materiasActivas2026 = [
            ['sigla' => 'SIS-201', 'paralelo' => 'A', 'p1' => 85.0, 'p2' => 88.0],
            ['sigla' => 'SIS-202', 'paralelo' => 'A', 'p1' => 91.0, 'p2' => 90.0],
            ['sigla' => 'MAT-201', 'paralelo' => 'A', 'p1' => 78.5, 'p2' => 82.0],
            ['sigla' => 'MAT-202', 'paralelo' => 'A', 'p1' => 89.0, 'p2' => 85.0],
        ];

        foreach ($materiasActivas2026 as $mAct) {
            if ($materias->has($mAct['sigla'])) {
                $cursoActivo = Curso::firstOrCreate(
                    [
                        'materia_id' => $materias[$mAct['sigla']]->id,
                        'gestion' => '2026-I',
                        'paralelo' => $mAct['paralelo'],
                    ],
                    [
                        'cupo_maximo' => 45,
                        'docente_id' => $docente1->id,
                        'fecha_inicio_calificacion' => now()->subDays(5),
                        'fecha_fin_calificacion' => now()->addDays(35),
                    ]
                );

                $ins = Inscripcion::create([
                    'estudiante_id' => $estudiante1->id,
                    'curso_id' => $cursoActivo->id,
                    'estado' => 'En Curso',
                    'tipo_registro' => 'Auto-Inscripción',
                ]);

                if ($primerParcial) {
                    CalificacionParcial::updateOrCreate(
                        ['inscripcion_id' => $ins->id, 'periodo_evaluacion_id' => $primerParcial->id],
                        ['nota' => $mAct['p1']]
                    );
                }
                if ($segundoParcial) {
                    CalificacionParcial::updateOrCreate(
                        ['inscripcion_id' => $ins->id, 'periodo_evaluacion_id' => $segundoParcial->id],
                        ['nota' => $mAct['p2']]
                    );
                }
                $ins->recalcularNotaFinal();
            }
        }

        // ==============================================================
        // 3. POBLAR ESTUDIANTES Y CALIFICACIONES EN CURSOS ACTIVOS
        // ==============================================================
        $todosEstudiantes = User::role('Estudiante')->get();
        $cursosDocente1 = $docente1->cursosDictados()->where('gestion', '2026-I')->get();

        // Garantizar que cada curso de docente1 tenga al menos 8 estudiantes con notas
        foreach ($cursosDocente1 as $cDoc) {
            foreach ($todosEstudiantes->take(8) as $idx => $est) {
                $ins = Inscripcion::firstOrCreate(
                    ['estudiante_id' => $est->id, 'curso_id' => $cDoc->id],
                    ['estado' => 'En Curso', 'tipo_registro' => 'Auto-Inscripción']
                );

                $notasSimuladas = [75.0, 84.5, 62.0, 91.0, 58.5, 88.0, 95.0, 70.0];
                if ($primerParcial) {
                    CalificacionParcial::updateOrCreate(
                        ['inscripcion_id' => $ins->id, 'periodo_evaluacion_id' => $primerParcial->id],
                        ['nota' => $notasSimuladas[$idx % count($notasSimuladas)]]
                    );
                }
                $ins->recalcularNotaFinal();
            }
        }

        // Distribuir estudiantes en el resto de los cursos de 2026-I
        $todosCursos2026 = Curso::where('gestion', '2026-I')->get();
        foreach ($todosCursos2026 as $curso) {
            if ($curso->inscripciones()->count() < 4) {
                $muestra = $todosEstudiantes->shuffle()->take(6);
                foreach ($muestra as $i => $est) {
                    $ins = Inscripcion::firstOrCreate(
                        ['estudiante_id' => $est->id, 'curso_id' => $curso->id],
                        ['estado' => 'En Curso', 'tipo_registro' => 'Auto-Inscripción']
                    );

                    if ($primerParcial && rand(1, 10) > 2) {
                        CalificacionParcial::updateOrCreate(
                            ['inscripcion_id' => $ins->id, 'periodo_evaluacion_id' => $primerParcial->id],
                            ['nota' => rand(60, 96)]
                        );
                        $ins->recalcularNotaFinal();
                    }
                }
            }
        }

        // ==============================================================
        // 4. DEJAR 1 CURSO SIN DOCENTE PARA DEMOSTRACIÓN EN VIVO
        // ==============================================================
        // Paralelo C de Cálculo I para que el presentador pueda hacer click en "Asignar Docente"
        if ($materias->has('MAT-101')) {
            Curso::updateOrCreate(
                [
                    'materia_id' => $materias['MAT-101']->id,
                    'gestion' => '2026-I',
                    'paralelo' => 'C',
                ],
                [
                    'docente_id' => null,
                    'cupo_maximo' => 40,
                    'fecha_inicio_calificacion' => now(),
                    'fecha_fin_calificacion' => now()->addDays(35),
                ]
            );
        }
    }
}
