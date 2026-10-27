<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostulanteEstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreraSistemas = Carrera::where('codigo', 'SIS')->first();
        $carreraInf = Carrera::where('codigo', 'INF')->first();

        // 1. POSTULANTES RECIENTES PARA GESTIÓN 2026-I
        $postulantesData = [
            // ADMITIDOS (Nota >= 61 o Excelencia Académica)
            [
                'name' => 'Juan Pérez Gómez',
                'ci' => '10293847',
                'email' => 'juan.perez@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 74.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Ana Condori Quispe',
                'ci' => '56473829',
                'email' => 'ana.condori@estudiante.test',
                'modalidad' => 'Excelencia Académica',
                'nota' => 95.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Brandon Siles Claure',
                'ci' => '78912340',
                'email' => 'brandon.siles@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 88.50,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Camila Vaca Justiniano',
                'ci' => '65412890',
                'email' => 'camila.vaca@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 72.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Diego Arratia Mercado',
                'ci' => '43219876',
                'email' => 'diego.arratia@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 61.00, // Corte exacto institucional
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Elena Gutiérrez Soliz',
                'ci' => '98765432',
                'email' => 'elena.gutierrez@estudiante.test',
                'modalidad' => 'Excelencia Académica',
                'nota' => 98.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Fabio Choque Mamani',
                'ci' => '87654321',
                'email' => 'fabio.choque@estudiante.test',
                'modalidad' => 'Traspaso',
                'nota' => 65.50,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Gisela Romero Pinto',
                'ci' => '76543210',
                'email' => 'gisela.romero@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 82.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Hugo Tórrez Balderrama',
                'ci' => '65432109',
                'email' => 'hugo.torrez@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 69.50,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Ivanna Daza Claros',
                'ci' => '54321098',
                'email' => 'ivanna.daza@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 77.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Joaquín Salguero Ortiz',
                'ci' => '43120987',
                'email' => 'joaquin.salguero@estudiante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 84.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraInf?->id ?? $carreraSistemas?->id,
            ],
            [
                'name' => 'Katia Morales Beltrán',
                'ci' => '32019876',
                'email' => 'katia.morales@estudiante.test',
                'modalidad' => 'Excelencia Académica',
                'nota' => 100.00,
                'estado' => 'Admitido',
                'carrera_id' => $carreraInf?->id ?? $carreraSistemas?->id,
            ],

            // NO ADMITIDOS (Nota < 61)
            [
                'name' => 'María López Cruz',
                'ci' => '84736251',
                'email' => 'maria.lopez@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 48.00,
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Jorge Céspedes Claros',
                'ci' => '32109876',
                'email' => 'jorge.cespedes@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 60.50, // A medio punto de aprobar
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Karen Ortiz Beltrán',
                'ci' => '21098765',
                'email' => 'karen.ortiz@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 42.00,
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Lucas Meneses Rocha',
                'ci' => '10987654',
                'email' => 'lucas.meneses@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 55.00,
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Mayra Soliz Zeballos',
                'ci' => '90876543',
                'email' => 'mayra.soliz@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 38.50,
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Nicolás Rivas Fuentes',
                'ci' => '80765432',
                'email' => 'nicolas.rivas@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => 29.00,
                'estado' => 'No Admitido',
                'carrera_id' => $carreraSistemas?->id,
            ],

            // PENDIENTES DE EVALUACIÓN (Sin nota registrada aún)
            [
                'name' => 'Rodrigo Morales Vega',
                'ci' => '92837465',
                'email' => 'rodrigo.morales@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Nicole Pardo Alarcón',
                'ci' => '89765432',
                'email' => 'nicole.pardo@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Oscar Viruez Salvatierra',
                'ci' => '78654321',
                'email' => 'oscar.viruez@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Paola Cárdenas Franco',
                'ci' => '67543210',
                'email' => 'paola.cardenas@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Renzo Baldivieso Peña',
                'ci' => '56432109',
                'email' => 'renzo.baldivieso@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'name' => 'Sofía Escalante Rios',
                'ci' => '45321098',
                'email' => 'sofia.escalante@postulante.test',
                'modalidad' => 'Examen de Admisión',
                'nota' => null,
                'estado' => 'Pendiente',
                'carrera_id' => $carreraSistemas?->id,
            ],
        ];

        foreach ($postulantesData as $pData) {
            $userP = User::firstOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'ci' => $pData['ci'],
                    'password' => bcrypt('password'),
                    'carrera_id' => $pData['carrera_id'],
                    'modalidad_admision' => $pData['modalidad'],
                    'nota_admision' => $pData['nota'],
                    'estado_admision' => $pData['estado'],
                    'fecha_admision' => ($pData['estado'] === 'Admitido') ? now()->subDays(rand(2, 10)) : (($pData['estado'] === 'No Admitido') ? now()->subDays(rand(1, 4)) : null),
                ]
            );

            if ($pData['estado'] === 'Admitido') {
                $userP->syncRoles(['Estudiante']);
            }
        }

        // 2. ESTUDIANTES REGULARES DE SEMESTRES AVANZADOS
        // (Para validar pre-requisitos cursados y aprobados)
        $estudiantesAvanzados = [
            [
                'email' => 'estudiante.nivel3@academico.test',
                'name' => 'Mauricio Montaño Terceros (3er Semestre)',
                'ci' => '30000001',
                'carrera_id' => $carreraSistemas?->id,
                'nota_admision' => 88.0,
                'estado_admision' => 'Admitido',
            ],
            [
                'email' => 'estudiante.nivel5@academico.test',
                'name' => 'Beatriz Loayza Villagómez (5to Semestre)',
                'ci' => '30000002',
                'carrera_id' => $carreraSistemas?->id,
                'nota_admision' => 92.0,
                'estado_admision' => 'Admitido',
            ],
            [
                'email' => 'estudiante.nivel8@academico.test',
                'name' => 'Alejandro Zeballos Claure (8vo Semestre)',
                'ci' => '30000003',
                'carrera_id' => $carreraSistemas?->id,
                'nota_admision' => 85.0,
                'estado_admision' => 'Admitido',
            ],
        ];

        foreach ($estudiantesAvanzados as $est) {
            $u = User::firstOrCreate(
                ['email' => $est['email']],
                [
                    'name' => $est['name'],
                    'ci' => $est['ci'],
                    'password' => bcrypt('password'),
                    'carrera_id' => $est['carrera_id'],
                    'modalidad_admision' => 'Examen de Admisión',
                    'nota_admision' => $est['nota_admision'],
                    'estado_admision' => $est['estado_admision'],
                    'fecha_admision' => now()->subYears(2),
                ]
            );
            $u->syncRoles(['Estudiante']);
        }
    }
}
