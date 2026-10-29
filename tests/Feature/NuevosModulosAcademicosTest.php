<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\PeriodoEvaluacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NuevosModulosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Director']);
        Role::firstOrCreate(['name' => 'Docente']);
        Role::firstOrCreate(['name' => 'Estudiante']);
        Role::firstOrCreate(['name' => 'Encargado de Kardex']);
    }

    public function test_prerequisito_debe_ser_estrictamente_de_nivel_inferior(): void
    {
        $carrera = Carrera::create(['nombre' => 'Ingeniería de Sistemas', 'codigo' => 'SIS', 'total_niveles' => 10]);
        $materiaNivel1 = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Cálculo I', 'sigla' => 'MAT-101', 'nivel' => 1]);
        $materiaNivel2 = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Cálculo II', 'sigla' => 'MAT-102', 'nivel' => 2]);
        $materiaNivel2Paralela = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Física II', 'sigla' => 'FIS-102', 'nivel' => 2]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);
        $admin->assignRole('Administrador');

        // 1. Permitido: Nivel 1 hacia Nivel 2
        $responsePermitido = $this->actingAs($admin)->postJson('/malla-curricular/toggle-requisito', [
            'materia_id' => $materiaNivel2->id,
            'requisito_id' => $materiaNivel1->id,
        ]);
        $responsePermitido->assertOk();
        $responsePermitido->assertJson(['success' => true]);

        // 2. Rechazado: Mismo nivel (Nivel 2 hacia Nivel 2)
        $responseMismoNivel = $this->actingAs($admin)->postJson('/malla-curricular/toggle-requisito', [
            'materia_id' => $materiaNivel2->id,
            'requisito_id' => $materiaNivel2Paralela->id,
        ]);
        $responseMismoNivel->assertStatus(422);

        // 3. Rechazado: Nivel superior como requisito (Nivel 2 hacia Nivel 1)
        $responseNivelSuperior = $this->actingAs($admin)->postJson('/malla-curricular/toggle-requisito', [
            'materia_id' => $materiaNivel1->id,
            'requisito_id' => $materiaNivel2->id,
        ]);
        $responseNivelSuperior->assertStatus(422);
    }

    public function test_modulo_docentes_permite_crear_y_listar(): void
    {
        $carrera = Carrera::create(['nombre' => 'Ingeniería de Sistemas', 'codigo' => 'SIS']);
        $admin = User::factory()->create(['carrera_id' => $carrera->id]);
        $admin->assignRole('Administrador');

        $response = $this->actingAs($admin)->post('/docentes', [
            'name' => 'Ing. Docente Nuevo',
            'ci' => '99887766',
            'email' => 'docente.nuevo@test.com',
            'carrera_id' => $carrera->id,
            'password' => 'password123',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'docente.nuevo@test.com', 'ci' => '99887766']);

        $docente = User::where('email', 'docente.nuevo@test.com')->first();
        $this->assertTrue($docente->hasRole('Docente'));
    }

    public function test_configuracion_academica_permite_modificar_semestres_y_parciales(): void
    {
        $carrera = Carrera::create(['nombre' => 'Informática', 'codigo' => 'INF', 'total_niveles' => 8]);
        $admin = User::factory()->create(['carrera_id' => $carrera->id]);
        $admin->assignRole('Administrador');

        // Actualizar niveles a 11
        $this->actingAs($admin)->put("/configuracion-academica/carreras/{$carrera->id}/niveles", [
            'total_niveles' => 11,
        ])->assertSessionHas('success');

        $this->assertEquals(11, $carrera->fresh()->total_niveles);

        // Crear periodo de evaluación con fechas
        $this->actingAs($admin)->post('/configuracion-academica/periodos', [
            'gestion' => '2026-I',
            'numero_parcial' => 1,
            'nombre' => '1er Parcial',
            'ponderacion' => 30.00,
            'fecha_inicio' => Carbon::now()->subDay()->toDateTimeString(),
            'fecha_fin' => Carbon::now()->addDays(5)->toDateTimeString(),
            'activo' => true,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('periodos_evaluacion', ['gestion' => '2026-I', 'numero_parcial' => 1]);
    }

    public function test_docente_no_puede_subir_notas_si_el_ciclo_esta_fuera_de_fecha(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $docente = User::factory()->create(['carrera_id' => $carrera->id]);
        $docente->assignRole('Docente');

        $materia = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Cálculo', 'sigla' => 'MAT-101', 'nivel' => 1]);
        $curso = Curso::create(['materia_id' => $materia->id, 'gestion' => '2026-I', 'paralelo' => 'A', 'docente_id' => $docente->id]);

        $estudiante = User::factory()->create(['carrera_id' => $carrera->id, 'estado_admision' => 'Admitido']);
        $estudiante->assignRole('Estudiante');
        $inscripcion = $estudiante->inscripciones()->create(['curso_id' => $curso->id, 'estado' => 'En Curso']);

        // Periodo CERRADO (finalizó ayer)
        $periodoCerrado = PeriodoEvaluacion::create([
            'gestion' => '2026-I',
            'numero_parcial' => 2,
            'nombre' => '2do Parcial',
            'ponderacion' => 35.00,
            'fecha_inicio' => Carbon::now()->subDays(10),
            'fecha_fin' => Carbon::now()->subDays(1),
            'activo' => true,
        ]);

        $response = $this->actingAs($docente)->post("/mis-cursos/{$curso->id}/guardar-notas", [
            'periodo_evaluacion_id' => $periodoCerrado->id,
            'notas' => [
                ['inscripcion_id' => $inscripcion->id, 'nota' => 80],
            ],
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('calificaciones_parciales', [
            'inscripcion_id' => $inscripcion->id,
            'periodo_evaluacion_id' => $periodoCerrado->id,
        ]);
    }

    public function test_docente_puede_subir_notas_en_periodo_habilitado_y_recalcula_final(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $docente = User::factory()->create(['carrera_id' => $carrera->id]);
        $docente->assignRole('Docente');

        $materia = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Programación', 'sigla' => 'SIS-101', 'nivel' => 1, 'nota_minima_aprobacion' => 51]);
        $curso = Curso::create(['materia_id' => $materia->id, 'gestion' => '2026-I', 'paralelo' => 'A', 'docente_id' => $docente->id]);

        $estudiante = User::factory()->create(['carrera_id' => $carrera->id, 'estado_admision' => 'Admitido']);
        $estudiante->assignRole('Estudiante');
        $inscripcion = $estudiante->inscripciones()->create(['curso_id' => $curso->id, 'estado' => 'En Curso']);

        // Periodo ABIERTO (actualmente válido)
        $periodoAbierto = PeriodoEvaluacion::create([
            'gestion' => '2026-I',
            'numero_parcial' => 1,
            'nombre' => '1er Parcial',
            'ponderacion' => 100.00, // para prueba simple de cálculo
            'fecha_inicio' => Carbon::now()->subDay(),
            'fecha_fin' => Carbon::now()->addDays(2),
            'activo' => true,
        ]);

        $response = $this->actingAs($docente)->post("/mis-cursos/{$curso->id}/guardar-notas", [
            'periodo_evaluacion_id' => $periodoAbierto->id,
            'notas' => [
                ['inscripcion_id' => $inscripcion->id, 'nota' => 85.0],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('calificaciones_parciales', [
            'inscripcion_id' => $inscripcion->id,
            'periodo_evaluacion_id' => $periodoAbierto->id,
            'nota' => 85.0,
        ]);

        $this->assertEquals(85.0, (float) $inscripcion->fresh()->nota_final);
        $this->assertEquals('Aprobado', $inscripcion->fresh()->estado);
    }

    public function test_listado_docentes_carga_correctamente_con_cursos_y_materias(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $docente = User::factory()->create(['carrera_id' => $carrera->id]);
        $docente->assignRole('Docente');

        $materia = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Programación', 'sigla' => 'SIS-101', 'nivel' => 1]);
        Curso::create(['materia_id' => $materia->id, 'gestion' => '2026-I', 'paralelo' => 'A', 'docente_id' => $docente->id]);

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $response = $this->actingAs($admin)->get('/docentes');
        $response->assertOk();
    }

    public function test_docente_puede_ver_mis_cursos(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $docente = User::factory()->create(['carrera_id' => $carrera->id]);
        $docente->assignRole('Docente');

        $materia = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Base de Datos', 'sigla' => 'SIS-201', 'nivel' => 2]);
        Curso::create(['materia_id' => $materia->id, 'gestion' => '2026-I', 'paralelo' => 'A', 'docente_id' => $docente->id]);

        $response = $this->actingAs($docente)->get('/mis-cursos');
        $response->assertOk();
    }

    public function test_estudiante_puede_ver_mi_panel(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $estudiante = User::factory()->create(['carrera_id' => $carrera->id]);
        $estudiante->assignRole('Estudiante');

        $materia = Materia::create(['carrera_id' => $carrera->id, 'nombre' => 'Redes', 'sigla' => 'SIS-301', 'nivel' => 3]);
        $curso = Curso::create(['materia_id' => $materia->id, 'gestion' => '2026-I', 'paralelo' => 'A']);
        $estudiante->inscripciones()->create(['curso_id' => $curso->id, 'estado' => 'En Curso']);

        $response = $this->actingAs($estudiante)->get('/mi-panel');
        $response->assertOk();
    }
}

