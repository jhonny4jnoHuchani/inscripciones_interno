<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $docentes = User::role('Docente')->get()->keyBy('email');

        $docCalculo = $docentes->get('docente.calculo@academico.test');
        $docProg = $docentes->get('docente.prog@academico.test');
        $docIa = $docentes->get('docente.ia@academico.test');
        $docRedes = $docentes->get('docente.redes@academico.test');
        $docBd = $docentes->get('docente.bd@academico.test');
        $docAlgebra = $docentes->get('docente.algebra@academico.test');
        $docFisica = $docentes->get('docente.fisica@academico.test');
        $docSoftware = $docentes->get('docente.software@academico.test');
        $docSo = $docentes->get('docente.so@academico.test');
        $docIngles = $docentes->get('docente.ingles@academico.test');
        $docOperativa = $docentes->get('docente.operativa@academico.test');
        $docSeguridad = $docentes->get('docente.seguridad@academico.test');

        $materias = Materia::all()->keyBy('sigla');

        // Configuración de Cursos para la Gestión 2026-I
        $cursos2026_I = [
            // NIVEL 1
            // Cálculo I (A, B, C)
            ['sigla' => 'MAT-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docCalculo?->id],
            ['sigla' => 'MAT-101', 'paralelo' => 'B', 'cupo' => 50, 'docente' => $docAlgebra?->id],
            ['sigla' => 'MAT-101', 'paralelo' => 'C', 'cupo' => 45, 'docente' => null], // Paralelo abierto disponible

            // Programación I (A, B, C)
            ['sigla' => 'SIS-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docProg?->id],
            ['sigla' => 'SIS-101', 'paralelo' => 'B', 'cupo' => 45, 'docente' => $docIa?->id],
            ['sigla' => 'SIS-101', 'paralelo' => 'C', 'cupo' => 40, 'docente' => $docSoftware?->id],

            // Física I (A, B)
            ['sigla' => 'FIS-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docFisica?->id],
            ['sigla' => 'FIS-101', 'paralelo' => 'B', 'cupo' => 45, 'docente' => $docIa?->id],

            // Álgebra y Geometría (A, B)
            ['sigla' => 'ALG-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docAlgebra?->id],
            ['sigla' => 'ALG-101', 'paralelo' => 'B', 'cupo' => 45, 'docente' => $docCalculo?->id],

            // Inglés Técnico I (A, B)
            ['sigla' => 'LIN-101', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docIngles?->id],
            ['sigla' => 'LIN-101', 'paralelo' => 'B', 'cupo' => 40, 'docente' => $docIngles?->id],

            // NIVEL 2
            ['sigla' => 'MAT-102', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docCalculo?->id],
            ['sigla' => 'MAT-102', 'paralelo' => 'B', 'cupo' => 40, 'docente' => $docAlgebra?->id],
            ['sigla' => 'SIS-102', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docProg?->id],
            ['sigla' => 'SIS-102', 'paralelo' => 'B', 'cupo' => 40, 'docente' => $docSoftware?->id],
            ['sigla' => 'FIS-102', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docFisica?->id],
            ['sigla' => 'MAT-103', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docAlgebra?->id],
            ['sigla' => 'LIN-102', 'paralelo' => 'A', 'cupo' => 40, 'docente' => $docIngles?->id],

            // NIVEL 3
            ['sigla' => 'SIS-201', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docProg?->id],
            ['sigla' => 'SIS-201', 'paralelo' => 'B', 'cupo' => 40, 'docente' => $docSoftware?->id],
            ['sigla' => 'SIS-202', 'paralelo' => 'A', 'cupo' => 40, 'docente' => $docSo?->id],
            ['sigla' => 'MAT-202', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docOperativa?->id],

            // NIVEL 4
            ['sigla' => 'SIS-301', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docBd?->id],
            ['sigla' => 'SIS-301', 'paralelo' => 'B', 'cupo' => 40, 'docente' => $docBd?->id],
            ['sigla' => 'SIS-302', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docSo?->id],
            ['sigla' => 'TEL-301', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docRedes?->id],

            // NIVEL 5
            ['sigla' => 'SIS-304', 'paralelo' => 'A', 'cupo' => 40, 'docente' => $docBd?->id],
            ['sigla' => 'SIS-306', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docSoftware?->id],
            ['sigla' => 'TEL-302', 'paralelo' => 'A', 'cupo' => 40, 'docente' => $docRedes?->id],

            // NIVEL 6
            ['sigla' => 'SIS-401', 'paralelo' => 'A', 'cupo' => 35, 'docente' => $docSoftware?->id],
            ['sigla' => 'SIS-403', 'paralelo' => 'A', 'cupo' => 35, 'docente' => $docSeguridad?->id],

            // NIVEL 8
            ['sigla' => 'SIS-501', 'paralelo' => 'A', 'cupo' => 35, 'docente' => $docIa?->id],
            ['sigla' => 'SIS-501', 'paralelo' => 'B', 'cupo' => 30, 'docente' => $docIa?->id],
            ['sigla' => 'SIS-503', 'paralelo' => 'A', 'cupo' => 25, 'docente' => $docSoftware?->id],
        ];

        foreach ($cursos2026_I as $cData) {
            if ($materias->has($cData['sigla'])) {
                Curso::firstOrCreate(
                    [
                        'materia_id' => $materias[$cData['sigla']]->id,
                        'gestion' => '2026-I',
                        'paralelo' => $cData['paralelo'],
                    ],
                    [
                        'docente_id' => $cData['docente'],
                        'cupo_maximo' => $cData['cupo'],
                        'fecha_inicio_calificacion' => now(),
                        'fecha_fin_calificacion' => now()->addDays(40),
                    ]
                );
            }
        }

        // Cursos Históricos para la Gestión 2025-II (para sustentar materias aprobadas de estudiantes regulares)
        $cursos2025_II = [
            ['sigla' => 'MAT-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docCalculo?->id],
            ['sigla' => 'SIS-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docProg?->id],
            ['sigla' => 'FIS-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docFisica?->id],
            ['sigla' => 'ALG-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docAlgebra?->id],
            ['sigla' => 'LIN-101', 'paralelo' => 'A', 'cupo' => 50, 'docente' => $docIngles?->id],
            ['sigla' => 'MAT-102', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docCalculo?->id],
            ['sigla' => 'SIS-102', 'paralelo' => 'A', 'cupo' => 45, 'docente' => $docProg?->id],
            ['sigla' => 'SIS-201', 'paralelo' => 'A', 'cupo' => 40, 'docente' => $docProg?->id],
        ];

        foreach ($cursos2025_II as $cData) {
            if ($materias->has($cData['sigla'])) {
                Curso::firstOrCreate(
                    [
                        'materia_id' => $materias[$cData['sigla']]->id,
                        'gestion' => '2025-II',
                        'paralelo' => $cData['paralelo'],
                    ],
                    [
                        'docente_id' => $cData['docente'],
                        'cupo_maximo' => $cData['cupo'],
                        'fecha_inicio_calificacion' => now()->subMonths(6),
                        'fecha_fin_calificacion' => now()->subMonths(4),
                    ]
                );
            }
        }
    }
}
