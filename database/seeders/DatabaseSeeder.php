<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database in logical dependency order.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CarreraSeeder::class,
            MateriaSeeder::class,
            DocenteSeeder::class,
            CursoSeeder::class,
            PeriodoEvaluacionSeeder::class,
            PortalWebSeeder::class,
            PostulanteEstudianteSeeder::class,
            InscripcionSeeder::class,
            DemoAccountsSeeder::class,
        ]);
    }
}
