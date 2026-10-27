<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdmisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Director', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
    }

    public function test_authenticated_user_can_view_admisiones_index(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
            'total_niveles' => 9,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->get('/admisiones');

        $response->assertStatus(200);
    }

    public function test_postulante_with_score_greater_or_equal_61_is_admitted_and_receives_estudiante_role(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);

        $postulante = User::create([
            'name' => 'Carlos Postulante',
            'ci' => '99887766',
            'email' => 'carlos@test.com',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'modalidad_admision' => 'Examen de Admisión',
            'estado_admision' => 'Pendiente',
        ]);

        $response = $this->actingAs($admin)->post("/admisiones/{$postulante->id}/nota", [
            'nota_admision' => 61.0,
            'modalidad_admision' => 'Examen de Admisión',
        ]);

        $response->assertSessionHas('success');

        $postulante->refresh();
        $this->assertEquals('Admitido', $postulante->estado_admision);
        $this->assertEquals(61.0, (float) $postulante->nota_admision);
        $this->assertTrue($postulante->estaAdmitido());
        $this->assertTrue($postulante->hasRole('Estudiante'));
    }

    public function test_postulante_with_score_less_than_61_is_not_admitted(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);

        $postulante = User::create([
            'name' => 'Pedro Reprobado',
            'ci' => '11223344',
            'email' => 'pedro@test.com',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'modalidad_admision' => 'Examen de Admisión',
            'estado_admision' => 'Pendiente',
        ]);

        $response = $this->actingAs($admin)->post("/admisiones/{$postulante->id}/nota", [
            'nota_admision' => 60.5,
            'modalidad_admision' => 'Examen de Admisión',
        ]);

        $response->assertSessionHas('info');

        $postulante->refresh();
        $this->assertEquals('No Admitido', $postulante->estado_admision);
        $this->assertEquals(60.5, (float) $postulante->nota_admision);
        $this->assertFalse($postulante->estaAdmitido());
        $this->assertFalse($postulante->hasRole('Estudiante'));
    }

    public function test_postulante_with_academic_excellence_is_automatically_admitted(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($admin)->post('/admisiones/postulantes', [
            'name' => 'Valeria Excelencia',
            'ci' => '77889900',
            'email' => 'valeria@test.com',
            'carrera_id' => $carrera->id,
            'modalidad_admision' => 'Excelencia Académica',
        ]);

        $response->assertSessionHas('success');

        $valeria = User::where('ci', '77889900')->first();
        $this->assertNotNull($valeria);
        $this->assertEquals('Admitido', $valeria->estado_admision);
        $this->assertTrue($valeria->estaAdmitido());
        $this->assertTrue($valeria->hasRole('Estudiante'));
    }

    public function test_cannot_manually_enroll_unadmitted_applicant(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $materia = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Cálculo I',
            'sigla' => 'MAT-101',
            'nivel' => 1,
        ]);

        $curso = Curso::create([
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 50,
        ]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);

        $noAdmitido = User::create([
            'name' => 'Luis Pendiente',
            'ci' => '33445566',
            'email' => 'luis@test.com',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'estado_admision' => 'Pendiente',
        ]);

        $response = $this->actingAs($admin)->post("/admisiones/{$noAdmitido->id}/inscribir-manual", [
            'curso_ids' => [$curso->id],
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('inscripciones', [
            'estudiante_id' => $noAdmitido->id,
            'curso_id' => $curso->id,
        ]);
    }

    public function test_registrar_can_manually_enroll_admitted_student_into_courses_as_kardex_manual(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $materia1 = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Cálculo I',
            'sigla' => 'MAT-101',
            'nivel' => 1,
        ]);

        $materia2 = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Programación I',
            'sigla' => 'SIS-101',
            'nivel' => 1,
        ]);

        $curso1 = Curso::create([
            'materia_id' => $materia1->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 50,
        ]);

        $curso2 = Curso::create([
            'materia_id' => $materia2->id,
            'gestion' => '2026-I',
            'paralelo' => 'B',
            'cupo_maximo' => 45,
        ]);

        $admin = User::factory()->create(['carrera_id' => $carrera->id]);

        $estudiante = User::create([
            'name' => 'Admitido Exitoso',
            'ci' => '55667788',
            'email' => 'admitido@test.com',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'nota_admision' => 85.0,
            'estado_admision' => 'Admitido',
        ]);
        $estudiante->assignRole('Estudiante');

        $response = $this->actingAs($admin)->post("/admisiones/{$estudiante->id}/inscribir-manual", [
            'curso_ids' => [$curso1->id, $curso2->id],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inscripciones', [
            'estudiante_id' => $estudiante->id,
            'curso_id' => $curso1->id,
            'tipo_registro' => 'Kardex Manual',
            'estado' => 'En Curso',
        ]);

        $this->assertDatabaseHas('inscripciones', [
            'estudiante_id' => $estudiante->id,
            'curso_id' => $curso2->id,
            'tipo_registro' => 'Kardex Manual',
            'estado' => 'En Curso',
        ]);
    }

    public function test_custom_permission_has_persisted_descripcion(): void
    {
        $perm = Permission::create([
            'name' => 'revisar_kardex_academico',
            'guard_name' => 'web',
            'descripcion' => 'Permite auditar el historial de notas y materias cursadas.',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'revisar_kardex_academico',
            'descripcion' => 'Permite auditar el historial de notas y materias cursadas.',
        ]);
    }

    public function test_can_create_dynamic_role_with_permissions(): void
    {
        $perm = Permission::create([
            'name' => 'aprobar_convalidacion',
            'guard_name' => 'web',
            'descripcion' => 'Homologar materias de otras universidades.',
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/roles', [
            'name' => 'Comisión de Convalidación',
            'permissions' => ['aprobar_convalidacion'],
        ]);

        $response->assertSessionHas('success');

        $role = Role::findByName('Comisión de Convalidación');
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('aprobar_convalidacion'));
    }
}
