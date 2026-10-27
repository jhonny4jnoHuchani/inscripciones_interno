<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;

class InscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. OBTENER ESTUDIANTES Y CURSOS
        $estudiantes = User::role('Estudiante')->get()->keyBy('email');
        $materias = Materia::all()->keyBy('sigla');

        // Función auxiliar para buscar curso por sigla, gestión y paralelo
        $getCurso = function (string $sigla, string $gestion, string $paralelo) use ($materias) {
            if (! isset($materias[$sigla])) {
                return null;
            }
            return Curso::where('materia_id', $materias[$sigla]->id)
                ->where('gestion', $gestion)
                ->where('paralelo', $paralelo)
                ->first();
        };

        // -------------------------------------------------------------
        // 2. HISTORIAL ACADÉMICO APROBADO (GESTIÓN 2025-II)
        // Permite validar que los estudiantes de semestres avanzados
        // ya cumplen los pre-requisitos de niveles anteriores.
        // -------------------------------------------------------------
        $estNivel3 = $estudiantes->get('estudiante.nivel3@academico.test');
        if ($estNivel3) {
            $materiasAprobadas = [
                ['sigla' => 'MAT-101', 'nota' => 78.0],
                ['sigla' => 'SIS-101', 'nota' => 84.5],
                ['sigla' => 'FIS-101', 'nota' => 62.0],
                ['sigla' => 'ALG-101', 'nota' => 71.0],
                ['sigla' => 'LIN-101', 'nota' => 90.0],
                ['sigla' => 'MAT-102', 'nota' => 65.0],
                ['sigla' => 'SIS-102', 'nota' => 88.0],
                ['sigla' => 'SIS-201', 'nota' => 74.0],
            ];

            foreach ($materiasAprobadas as $m) {
                $cursoHist = $getCurso($m['sigla'], '2025-II', 'A');
                if ($cursoHist) {
                    Inscripcion::firstOrCreate(
                        ['estudiante_id' => $estNivel3->id, 'curso_id' => $cursoHist->id],
                        [
                            'nota_final' => $m['nota'],
                            'estado' => 'Aprobado',
                            'tipo_registro' => 'Auto-Inscripción',
                        ]
                    );
                }
            }
        }

        // -------------------------------------------------------------
        // 3. INSCRIPCIONES ACTIVAS (GESTIÓN 2026-I)
        // Estudiantes recién admitidos con toma de materias de 1er semestre
        // -------------------------------------------------------------
        $inscripciones2026 = [
            // Juan Pérez Gómez (Kardex Manual)
            [
                'email' => 'juan.perez@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'A'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'A'],
                    ['sigla' => 'ALG-101', 'paralelo' => 'A'],
                ],
            ],
            // Ana Condori Quispe (Excelencia Académica, Kardex Manual)
            [
                'email' => 'ana.condori@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'B'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'B'],
                    ['sigla' => 'FIS-101', 'paralelo' => 'A'],
                    ['sigla' => 'ALG-101', 'paralelo' => 'B'],
                    ['sigla' => 'LIN-101', 'paralelo' => 'A'],
                ],
            ],
            // Brandon Siles Claure
            [
                'email' => 'brandon.siles@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'A'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'C'],
                    ['sigla' => 'FIS-101', 'paralelo' => 'A'],
                ],
            ],
            // Camila Vaca Justiniano
            [
                'email' => 'camila.vaca@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'B'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'A'],
                    ['sigla' => 'LIN-101', 'paralelo' => 'B'],
                ],
            ],
            // Diego Arratia Mercado (Admitido con 61.0 exacto)
            [
                'email' => 'diego.arratia@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'C'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'B'],
                ],
            ],
            // Elena Gutiérrez Soliz (Excelencia Académica)
            [
                'email' => 'elena.gutierrez@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'A'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'B'],
                    ['sigla' => 'FIS-101', 'paralelo' => 'B'],
                    ['sigla' => 'LIN-101', 'paralelo' => 'A'],
                ],
            ],
            // Fabio Choque Mamani (Traspaso)
            [
                'email' => 'fabio.choque@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'SIS-101', 'paralelo' => 'A'],
                    ['sigla' => 'ALG-101', 'paralelo' => 'A'],
                ],
            ],
            // Gisela Romero Pinto
            [
                'email' => 'gisela.romero@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'B'],
                    ['sigla' => 'FIS-101', 'paralelo' => 'A'],
                    ['sigla' => 'LIN-101', 'paralelo' => 'B'],
                ],
            ],
            // Hugo Tórrez Balderrama
            [
                'email' => 'hugo.torrez@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'A'],
                    ['sigla' => 'SIS-101', 'paralelo' => 'C'],
                ],
            ],
            // Ivanna Daza Claros
            [
                'email' => 'ivanna.daza@estudiante.test',
                'tipo' => 'Kardex Manual',
                'cursos' => [
                    ['sigla' => 'MAT-101', 'paralelo' => 'C'],
                    ['sigla' => 'ALG-101', 'paralelo' => 'B'],
                ],
            ],
        ];

        foreach ($inscripciones2026 as $insData) {
            $estudiante = $estudiantes->get($insData['email']);
            if (! $estudiante) {
                continue;
            }

            foreach ($insData['cursos'] as $cItem) {
                $curso = $getCurso($cItem['sigla'], '2026-I', $cItem['paralelo']);
                if ($curso) {
                    Inscripcion::firstOrCreate(
                        ['estudiante_id' => $estudiante->id, 'curso_id' => $curso->id],
                        [
                            'nota_final' => null,
                            'estado' => 'En Curso',
                            'tipo_registro' => $insData['tipo'],
                        ]
                    );
                }
            }
        }
    }
}
