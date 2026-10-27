<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreraSistemas = Carrera::where('codigo', 'SIS')->first();
        $carreraInformatica = Carrera::where('codigo', 'INF')->first();
        $carreraTel = Carrera::where('codigo', 'TEL')->first();

        // 1. Directores y Administradores
        $admin = User::firstOrCreate(
            ['email' => 'admin@academico.test'],
            [
                'name' => 'Ing. Alejandro Villarroel (Super Administrador)',
                'ci' => '10000001',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $admin->syncRoles(['Administrador']);

        $dirSistemas = User::firstOrCreate(
            ['email' => 'director@academico.test'],
            [
                'name' => 'Ing. Carlos Mendoza (Director Sistemas)',
                'ci' => '10000002',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $dirSistemas->syncRoles(['Director']);
        $carreraSistemas?->update(['director_id' => $dirSistemas->id]);

        $dirInf = User::firstOrCreate(
            ['email' => 'director.informatica@academico.test'],
            [
                'name' => 'M.Sc. Patricia Zambrana (Directora Informática)',
                'ci' => '10000003',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraInformatica?->id,
            ]
        );
        $dirInf->syncRoles(['Director']);
        $carreraInformatica?->update(['director_id' => $dirInf->id]);

        $kardexUser = User::firstOrCreate(
            ['email' => 'kardex@academico.test'],
            [
                'name' => 'Lic. Gonzalo Valenzuela (Encargado Kardex)',
                'ci' => '10000004',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $kardexUser->syncRoles(['Encargado de Kardex']);

        $admisionUser = User::firstOrCreate(
            ['email' => 'admisiones@academico.test'],
            [
                'name' => 'Ing. Roberto Paz (Encargado de Admisiones)',
                'ci' => '10000005',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $admisionUser->syncRoles(['Encargado de Kardex']);

        $comisionUser = User::firstOrCreate(
            ['email' => 'comision@academico.test'],
            [
                'name' => 'Lic. Elena Arze (Comisión Académica)',
                'ci' => '10000006',
                'password' => bcrypt('password'),
                'carrera_id' => $carreraSistemas?->id,
            ]
        );
        $comisionUser->syncRoles(['Comisión Académica']);

        // 2. Docentes Especialistas (12 docentes)
        $docentesData = [
            [
                'email' => 'docente.calculo@academico.test',
                'name' => 'Lic. Roberto Gómez Soliz',
                'ci' => '20000001',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.prog@academico.test',
                'name' => 'Ing. Laura Silva Montaño',
                'ci' => '20000002',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.ia@academico.test',
                'name' => 'M.Sc. Fernando Rojas Claros',
                'ci' => '20000003',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.redes@academico.test',
                'name' => 'Dr. Marcelo Torrico Alarcón',
                'ci' => '20000004',
                'carrera_id' => $carreraTel?->id ?? $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.bd@academico.test',
                'name' => 'Ing. Gabriela Benítez Claure',
                'ci' => '20000005',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.algebra@academico.test',
                'name' => 'Lic. Ramiro Paredes Vaca',
                'ci' => '20000006',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.fisica@academico.test',
                'name' => 'M.Sc. Ernesto Justiniano Peña',
                'ci' => '20000007',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.software@academico.test',
                'name' => 'Ing. Claudia Villarroel Vega',
                'ci' => '20000008',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.so@academico.test',
                'name' => 'Ing. Javier Arnez Terán',
                'ci' => '20000009',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.ingles@academico.test',
                'name' => 'Lic. Stephanie Taylor Wood',
                'ci' => '20000010',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.operativa@academico.test',
                'name' => 'Dr. Wilson Balderrama Cruz',
                'ci' => '20000011',
                'carrera_id' => $carreraSistemas?->id,
            ],
            [
                'email' => 'docente.seguridad@academico.test',
                'name' => 'M.Sc. Daniel Meneses Paz',
                'ci' => '20000012',
                'carrera_id' => $carreraSistemas?->id,
            ],
        ];

        foreach ($docentesData as $doc) {
            $userDoc = User::firstOrCreate(
                ['email' => $doc['email']],
                [
                    'name' => $doc['name'],
                    'ci' => $doc['ci'],
                    'password' => bcrypt('password'),
                    'carrera_id' => $doc['carrera_id'],
                ]
            );
            $userDoc->syncRoles(['Docente']);
        }
    }
}
