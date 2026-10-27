<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreraSistemas = Carrera::where('codigo', 'SIS')->first();
        $carreraInformatica = Carrera::where('codigo', 'INF')->first();

        if (! $carreraSistemas) {
            return;
        }

        // Definición de materias para Ingeniería de Sistemas
        $materiasSistemas = [
            // Nivel 1
            ['sigla' => 'MAT-101', 'nombre' => 'Cálculo I', 'nivel' => 1, 'nota' => 51, 'requisitos' => []],
            ['sigla' => 'SIS-101', 'nombre' => 'Programación I', 'nivel' => 1, 'nota' => 51, 'requisitos' => []],
            ['sigla' => 'FIS-101', 'nombre' => 'Física I', 'nivel' => 1, 'nota' => 51, 'requisitos' => []],
            ['sigla' => 'ALG-101', 'nombre' => 'Álgebra y Geometría Analítica', 'nivel' => 1, 'nota' => 51, 'requisitos' => []],
            ['sigla' => 'LIN-101', 'nombre' => 'Inglés Técnico I', 'nivel' => 1, 'nota' => 51, 'requisitos' => []],

            // Nivel 2
            ['sigla' => 'MAT-102', 'nombre' => 'Cálculo II', 'nivel' => 2, 'nota' => 51, 'requisitos' => ['MAT-101']],
            ['sigla' => 'SIS-102', 'nombre' => 'Programación II', 'nivel' => 2, 'nota' => 51, 'requisitos' => ['SIS-101']],
            ['sigla' => 'FIS-102', 'nombre' => 'Física II', 'nivel' => 2, 'nota' => 51, 'requisitos' => ['FIS-101']],
            ['sigla' => 'MAT-103', 'nombre' => 'Álgebra Lineal', 'nivel' => 2, 'nota' => 51, 'requisitos' => ['ALG-101']],
            ['sigla' => 'LIN-102', 'nombre' => 'Inglés Técnico II', 'nivel' => 2, 'nota' => 51, 'requisitos' => ['LIN-101']],

            // Nivel 3
            ['sigla' => 'SIS-201', 'nombre' => 'Estructura de Datos', 'nivel' => 3, 'nota' => 51, 'requisitos' => ['SIS-102']],
            ['sigla' => 'SIS-202', 'nombre' => 'Arquitectura de Computadoras', 'nivel' => 3, 'nota' => 51, 'requisitos' => ['FIS-102']],
            ['sigla' => 'MAT-201', 'nombre' => 'Ecuaciones Diferenciales', 'nivel' => 3, 'nota' => 51, 'requisitos' => ['MAT-102']],
            ['sigla' => 'MAT-202', 'nombre' => 'Probabilidad y Estadística', 'nivel' => 3, 'nota' => 51, 'requisitos' => ['MAT-102']],
            ['sigla' => 'SIS-203', 'nombre' => 'Técnicas de Programación Orientada a Objetos', 'nivel' => 3, 'nota' => 51, 'requisitos' => ['SIS-102']],

            // Nivel 4
            ['sigla' => 'SIS-301', 'nombre' => 'Base de Datos I', 'nivel' => 4, 'nota' => 51, 'requisitos' => ['SIS-201']],
            ['sigla' => 'SIS-302', 'nombre' => 'Sistemas Operativos', 'nivel' => 4, 'nota' => 51, 'requisitos' => ['SIS-202']],
            ['sigla' => 'SIS-303', 'nombre' => 'Análisis y Diseño de Sistemas I', 'nivel' => 4, 'nota' => 51, 'requisitos' => ['SIS-203']],
            ['sigla' => 'MAT-301', 'nombre' => 'Métodos Numéricos', 'nivel' => 4, 'nota' => 51, 'requisitos' => ['MAT-103', 'MAT-201']],
            ['sigla' => 'TEL-301', 'nombre' => 'Redes de Computadoras I', 'nivel' => 4, 'nota' => 51, 'requisitos' => ['SIS-202']],

            // Nivel 5
            ['sigla' => 'SIS-304', 'nombre' => 'Base de Datos II', 'nivel' => 5, 'nota' => 51, 'requisitos' => ['SIS-301']],
            ['sigla' => 'SIS-305', 'nombre' => 'Análisis y Diseño de Sistemas II', 'nivel' => 5, 'nota' => 51, 'requisitos' => ['SIS-303']],
            ['sigla' => 'SIS-306', 'nombre' => 'Programación Web y Móvil', 'nivel' => 5, 'nota' => 51, 'requisitos' => ['SIS-301']],
            ['sigla' => 'TEL-302', 'nombre' => 'Redes de Computadoras II', 'nivel' => 5, 'nota' => 51, 'requisitos' => ['TEL-301']],
            ['sigla' => 'SIS-307', 'nombre' => 'Investigación Operativa', 'nivel' => 5, 'nota' => 51, 'requisitos' => ['MAT-202']],

            // Nivel 6
            ['sigla' => 'SIS-401', 'nombre' => 'Ingeniería de Software I', 'nivel' => 6, 'nota' => 51, 'requisitos' => ['SIS-305']],
            ['sigla' => 'SIS-402', 'nombre' => 'Sistemas de Información Gerencial', 'nivel' => 6, 'nota' => 51, 'requisitos' => ['SIS-304']],
            ['sigla' => 'SIS-403', 'nombre' => 'Seguridad Informática y Criptografía', 'nivel' => 6, 'nota' => 51, 'requisitos' => ['TEL-302', 'SIS-302']],
            ['sigla' => 'SIS-404', 'nombre' => 'Simulación de Sistemas', 'nivel' => 6, 'nota' => 51, 'requisitos' => ['SIS-307']],

            // Nivel 7
            ['sigla' => 'SIS-405', 'nombre' => 'Ingeniería de Software II', 'nivel' => 7, 'nota' => 51, 'requisitos' => ['SIS-401']],
            ['sigla' => 'SIS-406', 'nombre' => 'Gestión y Auditoría de Sistemas', 'nivel' => 7, 'nota' => 51, 'requisitos' => ['SIS-401']],
            ['sigla' => 'SIS-407', 'nombre' => 'Inteligencia de Negocios y Big Data', 'nivel' => 7, 'nota' => 51, 'requisitos' => ['SIS-304']],

            // Nivel 8
            ['sigla' => 'SIS-501', 'nombre' => 'Inteligencia Artificial', 'nivel' => 8, 'nota' => 61, 'requisitos' => ['SIS-201', 'MAT-103']],
            ['sigla' => 'SIS-502', 'nombre' => 'Robótica y Sistemas Embebidos', 'nivel' => 8, 'nota' => 51, 'requisitos' => ['SIS-202', 'SIS-501']],
            ['sigla' => 'SIS-503', 'nombre' => 'Taller de Grado I', 'nivel' => 8, 'nota' => 61, 'requisitos' => ['SIS-405']],

            // Nivel 9
            ['sigla' => 'SIS-504', 'nombre' => 'Taller de Grado II (Proyecto Final)', 'nivel' => 9, 'nota' => 61, 'requisitos' => ['SIS-503']],
            ['sigla' => 'SIS-505', 'nombre' => 'Práctica Profesional Supervisada', 'nivel' => 9, 'nota' => 51, 'requisitos' => ['SIS-405']],
            ['sigla' => 'SIS-506', 'nombre' => 'Ética Profesional y Deontología', 'nivel' => 9, 'nota' => 51, 'requisitos' => ['SIS-406']],
        ];

        // 1. Crear todas las materias de Sistemas
        $modelosCreados = [];
        foreach ($materiasSistemas as $mData) {
            $m = Materia::firstOrCreate(
                ['sigla' => $mData['sigla']],
                [
                    'carrera_id' => $carreraSistemas->id,
                    'nombre' => $mData['nombre'],
                    'nivel' => $mData['nivel'],
                    'nota_minima_aprobacion' => $mData['nota'],
                ]
            );
            $modelosCreados[$mData['sigla']] = $m;
        }

        // 2. Asociar pre-requisitos
        foreach ($materiasSistemas as $mData) {
            if (! empty($mData['requisitos'])) {
                $idsRequisitos = [];
                foreach ($mData['requisitos'] as $reqSigla) {
                    if (isset($modelosCreados[$reqSigla])) {
                        $idsRequisitos[] = $modelosCreados[$reqSigla]->id;
                    }
                }
                if (! empty($idsRequisitos)) {
                    $modelosCreados[$mData['sigla']]->requisitos()->syncWithoutDetaching($idsRequisitos);
                }
            }
        }

        // 3. Materias para Ingeniería Informática
        if ($carreraInformatica) {
            $materiasInf = [
                ['sigla' => 'INF-101', 'nombre' => 'Fundamentos de Computación', 'nivel' => 1, 'nota' => 51],
                ['sigla' => 'INF-102', 'nombre' => 'Introducción a la Programación', 'nivel' => 1, 'nota' => 51],
                ['sigla' => 'MAT-111', 'nombre' => 'Álgebra Básica', 'nivel' => 1, 'nota' => 51],
                ['sigla' => 'INF-201', 'nombre' => 'Programación Orientada a Objetos', 'nivel' => 2, 'nota' => 51],
                ['sigla' => 'INF-202', 'nombre' => 'Estructura de Datos y Algoritmos', 'nivel' => 3, 'nota' => 51],
                ['sigla' => 'INF-301', 'nombre' => 'Bases de Datos Relacionales', 'nivel' => 4, 'nota' => 51],
                ['sigla' => 'INF-401', 'nombre' => 'Desarrollo de Software Distribuido', 'nivel' => 5, 'nota' => 51],
            ];

            foreach ($materiasInf as $infData) {
                Materia::firstOrCreate(
                    ['sigla' => $infData['sigla']],
                    [
                        'carrera_id' => $carreraInformatica->id,
                        'nombre' => $infData['nombre'],
                        'nivel' => $infData['nivel'],
                        'nota_minima_aprobacion' => $infData['nota'],
                    ]
                );
            }
        }
    }
}
