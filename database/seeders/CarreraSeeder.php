<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carreras = [
            [
                'codigo' => 'SIS',
                'nombre' => 'Ingeniería de Sistemas',
                'total_niveles' => 9,
            ],
            [
                'codigo' => 'INF',
                'nombre' => 'Ingeniería Informática',
                'total_niveles' => 9,
            ],
            [
                'codigo' => 'TEL',
                'nombre' => 'Ingeniería de Redes y Telecomunicaciones',
                'total_niveles' => 10,
            ],
            [
                'codigo' => 'SOF',
                'nombre' => 'Ingeniería de Software',
                'total_niveles' => 9,
            ],
        ];

        foreach ($carreras as $c) {
            Carrera::firstOrCreate(
                ['codigo' => $c['codigo']],
                [
                    'nombre' => $c['nombre'],
                    'total_niveles' => $c['total_niveles'],
                ]
            );
        }
    }
}
