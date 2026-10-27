<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $roles = ['Administrador', 'Director', 'Docente', 'Estudiante'];
        $roleModels = [];
        foreach ($roles as $role) {
            $roleModels[$role] = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Permisos con descripción explicativa
        $permisosData = [
            [
                'name' => 'gestionar_admisiones',
                'descripcion' => 'Registrar postulantes y asentar notas del examen de admisión (mínimo 61 para aprobar).',
            ],
            [
                'name' => 'inscribir_estudiante_manual',
                'descripcion' => 'Inscribir manualmente estudiantes por Kardex seleccionando materias y paralelos.',
            ],
            [
                'name' => 'configurar_fechas_inscripcion',
                'descripcion' => 'Habilitar y cerrar periodos de inscripción semestral.',
            ],
            [
                'name' => 'administrar_roles_permisos',
                'descripcion' => 'Crear y gestionar roles dinámicos y asignar permisos del sistema.',
            ],
            [
                'name' => 'gestionar_malla',
                'descripcion' => 'Diseñar y actualizar la malla curricular y pre-requisitos de materias.',
            ],
            [
                'name' => 'gestionar_cursos',
                'descripcion' => 'Aperturar paralelos, definir cupos máximos y asignar docentes.',
            ],
        ];

        foreach ($permisosData as $p) {
            $perm = \App\Models\Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                ['descripcion' => $p['descripcion']]
            );
            // Actualizar descripción si ya existía sin ella
            if ($perm->descripcion !== $p['descripcion']) {
                $perm->update(['descripcion' => $p['descripcion']]);
            }
        }

        // Asignar permisos a Administrador y Director
        $roleModels['Administrador']->givePermissionTo(\App\Models\Permission::all());
        $roleModels['Director']->givePermissionTo([
            'gestionar_admisiones',
            'inscribir_estudiante_manual',
            'configurar_fechas_inscripcion',
            'gestionar_malla',
            'gestionar_cursos',
        ]);

        // Crear o actualizar Carrera principal
        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'SIS'],
            [
                'nombre' => 'Ingeniería de Sistemas',
                'total_niveles' => 9,
            ]
        );

        // Crear Director de prueba
        $director = User::firstOrCreate(
            ['email' => 'director@academico.test'],
            [
                'name' => 'Ing. Carlos Mendoza (Director)',
                'password' => bcrypt('password'),
                'carrera_id' => $carrera->id,
            ]
        );
        $director->assignRole('Director');
        $carrera->update(['director_id' => $director->id]);

        // Crear Docentes de prueba
        $docente1 = User::firstOrCreate(
            ['email' => 'docente1@academico.test'],
            ['name' => 'Lic. Roberto Gómez', 'password' => bcrypt('password'), 'carrera_id' => $carrera->id]
        );
        $docente1->assignRole('Docente');

        $docente2 = User::firstOrCreate(
            ['email' => 'docente2@academico.test'],
            ['name' => 'Ing. Laura Silva', 'password' => bcrypt('password'), 'carrera_id' => $carrera->id]
        );
        $docente2->assignRole('Docente');

        $docente3 = User::firstOrCreate(
            ['email' => 'docente3@academico.test'],
            ['name' => 'M.Sc. Fernando Rojas', 'password' => bcrypt('password'), 'carrera_id' => $carrera->id]
        );
        $docente3->assignRole('Docente');

        // Materias Nivel 1
        $calc1 = Materia::firstOrCreate(
            ['sigla' => 'MAT-101'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Cálculo I', 'nota_minima_aprobacion' => 51, 'nivel' => 1]
        );
        $prog1 = Materia::firstOrCreate(
            ['sigla' => 'SIS-101'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Programación I', 'nota_minima_aprobacion' => 51, 'nivel' => 1]
        );
        $fis1 = Materia::firstOrCreate(
            ['sigla' => 'FIS-101'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Física I', 'nota_minima_aprobacion' => 51, 'nivel' => 1]
        );

        // Materias Nivel 2
        $calc2 = Materia::firstOrCreate(
            ['sigla' => 'MAT-102'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Cálculo II', 'nota_minima_aprobacion' => 51, 'nivel' => 2]
        );
        $prog2 = Materia::firstOrCreate(
            ['sigla' => 'SIS-102'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Programación II', 'nota_minima_aprobacion' => 51, 'nivel' => 2]
        );
        $fis2 = Materia::firstOrCreate(
            ['sigla' => 'FIS-102'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Física II', 'nota_minima_aprobacion' => 51, 'nivel' => 2]
        );

        // Materias Nivel 3
        $estDatos = Materia::firstOrCreate(
            ['sigla' => 'SIS-201'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Estructura de Datos', 'nota_minima_aprobacion' => 51, 'nivel' => 3]
        );
        $algLineal = Materia::firstOrCreate(
            ['sigla' => 'MAT-201'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Álgebra Lineal', 'nota_minima_aprobacion' => 51, 'nivel' => 3]
        );

        // Materias Nivel 4
        $bd1 = Materia::firstOrCreate(
            ['sigla' => 'SIS-301'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Base de Datos I', 'nota_minima_aprobacion' => 51, 'nivel' => 4]
        );

        // Materia Nivel 8 (demostración de enlace entre niveles distantes)
        $ia = Materia::firstOrCreate(
            ['sigla' => 'SIS-501'],
            ['carrera_id' => $carrera->id, 'nombre' => 'Inteligencia Artificial', 'nota_minima_aprobacion' => 61, 'nivel' => 8]
        );

        // Vincular pre-requisitos
        $calc2->requisitos()->syncWithoutDetaching([$calc1->id]);
        $prog2->requisitos()->syncWithoutDetaching([$prog1->id]);
        $fis2->requisitos()->syncWithoutDetaching([$fis1->id]);
        $estDatos->requisitos()->syncWithoutDetaching([$prog2->id]);
        $algLineal->requisitos()->syncWithoutDetaching([$calc1->id]);
        $bd1->requisitos()->syncWithoutDetaching([$estDatos->id]);
        $ia->requisitos()->syncWithoutDetaching([$estDatos->id, $algLineal->id]);

        // Crear Cursos / Paralelos para gestión 2026-I
        Curso::firstOrCreate(
            ['materia_id' => $calc1->id, 'gestion' => '2026-I', 'paralelo' => 'A'],
            [
                'docente_id' => $docente1->id,
                'cupo_maximo' => 50,
                'fecha_inicio_calificacion' => now(),
                'fecha_fin_calificacion' => now()->addDays(30),
            ]
        );

        Curso::firstOrCreate(
            ['materia_id' => $calc1->id, 'gestion' => '2026-I', 'paralelo' => 'B'],
            [
                'docente_id' => $docente2->id,
                'cupo_maximo' => 50,
                'fecha_inicio_calificacion' => now(),
                'fecha_fin_calificacion' => now()->addDays(30),
            ]
        );

        Curso::firstOrCreate(
            ['materia_id' => $calc1->id, 'gestion' => '2026-I', 'paralelo' => 'C'],
            [
                'docente_id' => null, // Paralelo abierto pendiente de asignación
                'cupo_maximo' => 45,
                'fecha_inicio_calificacion' => now(),
                'fecha_fin_calificacion' => now()->addDays(30),
            ]
        );

        // Docente 3 enseña Programación I (Nivel 1) y también Inteligencia Artificial (Nivel 8)
        Curso::firstOrCreate(
            ['materia_id' => $prog1->id, 'gestion' => '2026-I', 'paralelo' => 'A'],
            [
                'docente_id' => $docente3->id,
                'cupo_maximo' => 50,
                'fecha_inicio_calificacion' => now(),
                'fecha_fin_calificacion' => now()->addDays(30),
            ]
        );

        Curso::firstOrCreate(
            ['materia_id' => $ia->id, 'gestion' => '2026-I', 'paralelo' => 'A'],
            [
                'docente_id' => $docente3->id,
                'cupo_maximo' => 35,
                'fecha_inicio_calificacion' => now(),
                'fecha_fin_calificacion' => now()->addDays(30),
            ]
        );

        // Postulantes / Estudiantes de muestra para Admisiones y Kardex
        $estudianteAdmitido = User::firstOrCreate(
            ['email' => 'juan.perez@estudiante.test'],
            [
                'name' => 'Juan Pérez Gómez',
                'ci' => '10293847',
                'password' => bcrypt('password'),
                'carrera_id' => $carrera->id,
                'modalidad_admision' => 'Examen de Admisión',
                'nota_admision' => 74.00,
                'estado_admision' => 'Admitido',
                'fecha_admision' => now()->subDays(5),
            ]
        );
        $estudianteAdmitido->assignRole('Estudiante');

        $estudianteExcelencia = User::firstOrCreate(
            ['email' => 'ana.condori@estudiante.test'],
            [
                'name' => 'Ana Condori Quispe',
                'ci' => '56473829',
                'password' => bcrypt('password'),
                'carrera_id' => $carrera->id,
                'modalidad_admision' => 'Excelencia Académica',
                'nota_admision' => 95.00,
                'estado_admision' => 'Admitido',
                'fecha_admision' => now()->subDays(7),
            ]
        );
        $estudianteExcelencia->assignRole('Estudiante');

        User::firstOrCreate(
            ['email' => 'maria.lopez@postulante.test'],
            [
                'name' => 'María López Cruz',
                'ci' => '84736251',
                'password' => bcrypt('password'),
                'carrera_id' => $carrera->id,
                'modalidad_admision' => 'Examen de Admisión',
                'nota_admision' => 48.00,
                'estado_admision' => 'No Admitido',
                'fecha_admision' => now()->subDays(2),
            ]
        );

        User::firstOrCreate(
            ['email' => 'rodrigo.morales@postulante.test'],
            [
                'name' => 'Rodrigo Morales Vega',
                'ci' => '92837465',
                'password' => bcrypt('password'),
                'carrera_id' => $carrera->id,
                'modalidad_admision' => 'Examen de Admisión',
                'nota_admision' => null,
                'estado_admision' => 'Pendiente',
                'fecha_admision' => null,
            ]
        );
    }
}
