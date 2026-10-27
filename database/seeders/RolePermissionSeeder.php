<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles del Sistema
        $roles = [
            'Administrador',
            'Director',
            'Encargado de Kardex',
            'Comisión Académica',
            'Docente',
            'Estudiante',
        ];

        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Permisos con descripción detallada
        $permisos = [
            [
                'name' => 'gestionar_admisiones',
                'descripcion' => 'Registrar postulantes, registrar actas y asentar notas del examen de admisión con corte en 61 puntos.',
            ],
            [
                'name' => 'inscribir_estudiante_manual',
                'descripcion' => 'Inscribir y desinscribir manualmente a estudiantes admitidos por Kardex seleccionando materias y paralelos.',
            ],
            [
                'name' => 'configurar_fechas_inscripcion',
                'descripcion' => 'Habilitar, prorrogar o clausurar calendarios de inscripción regular y extemporánea.',
            ],
            [
                'name' => 'administrar_roles_permisos',
                'descripcion' => 'Crear, renombrar roles y definir la matriz de permisos descriptivos del sistema.',
            ],
            [
                'name' => 'gestionar_malla',
                'descripcion' => 'Diseñar la malla curricular, crear materias, definir niveles y configurar dependencias de pre-requisitos.',
            ],
            [
                'name' => 'gestionar_cursos',
                'descripcion' => 'Aperturar paralelos semestrales (A, B, C, D), asignar cupos máximos y definir fechas de calificación.',
            ],
            [
                'name' => 'asignar_docentes',
                'descripcion' => 'Vincular y reasignar docentes a paralelos abiertos cuidando restricciones y carga horaria.',
            ],
            [
                'name' => 'calificar_actas',
                'descripcion' => 'Registrar evaluaciones continuas, exámenes finales y asentar notas definitivas de estudiantes.',
            ],
            [
                'name' => 'modificar_notas_cerradas',
                'descripcion' => 'Emitir rectificaciones y resoluciones de notas posteriores al cierre formal del acta académica.',
            ],
            [
                'name' => 'auditar_historial_academico',
                'descripcion' => 'Consultar el historial completo de kárdex, materias aprobadas, reprobadas y promedio ponderado.',
            ],
            [
                'name' => 'emitir_certificados',
                'descripcion' => 'Generar certificados de notas, constancias de estudio y certificados de conclusión de carrera.',
            ],
            [
                'name' => 'aprobar_convalidaciones',
                'descripcion' => 'Dictaminar homologación y convalidación de materias cursadas en otras universidades o carreras.',
            ],
            [
                'name' => 'gestionar_carreras',
                'descripcion' => 'Crear y actualizar carreras académicas, cantidad de semestres y designar directores de carrera.',
            ],
            [
                'name' => 'reportes_estadisticos',
                'descripcion' => 'Visualizar reportes ejecutivos de tasas de aprobación, deserción, demanda de paralelos y rendimiento docente.',
            ],
        ];

        foreach ($permisos as $p) {
            $perm = Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                ['descripcion' => $p['descripcion']]
            );
            if ($perm->descripcion !== $p['descripcion']) {
                $perm->update(['descripcion' => $p['descripcion']]);
            }
        }

        // 3. Asignación de Permisos a Roles
        // Administrador tiene acceso total
        $roleModels['Administrador']->syncPermissions(Permission::all());

        // Director de Carrera
        $roleModels['Director']->syncPermissions([
            'gestionar_admisiones',
            'inscribir_estudiante_manual',
            'configurar_fechas_inscripcion',
            'gestionar_malla',
            'gestionar_cursos',
            'asignar_docentes',
            'auditar_historial_academico',
            'emitir_certificados',
            'aprobar_convalidaciones',
            'reportes_estadisticos',
        ]);

        // Encargado de Kardex
        $roleModels['Encargado de Kardex']->syncPermissions([
            'gestionar_admisiones',
            'inscribir_estudiante_manual',
            'auditar_historial_academico',
            'emitir_certificados',
        ]);

        // Comisión Académica
        $roleModels['Comisión Académica']->syncPermissions([
            'aprobar_convalidaciones',
            'modificar_notas_cerradas',
            'reportes_estadisticos',
        ]);

        // Docente
        $roleModels['Docente']->syncPermissions([
            'calificar_actas',
        ]);

        // Estudiante (permisos de consulta básica sin permisos administrativos)
        $roleModels['Estudiante']->syncPermissions([]);
    }
}
