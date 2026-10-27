<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_cursos_index(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
            'total_niveles' => 9,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->get('/cursos');

        $response->assertStatus(200);
    }

    public function test_can_open_new_paralelo_with_cupo(): void
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
            'nota_minima_aprobacion' => 51,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->post('/cursos', [
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 50,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cursos', [
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 50,
        ]);
    }

    public function test_cannot_open_duplicate_paralelo_in_same_gestion(): void
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
            'nota_minima_aprobacion' => 51,
        ]);

        Curso::create([
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 50,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        // Intentar crear nuevamente el paralelo 'A' para la misma materia y gestión
        $response = $this->actingAs($user)->post('/cursos', [
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'A',
            'cupo_maximo' => 45,
        ]);

        $response->assertSessionHasErrors(['paralelo']);
    }

    public function test_can_assign_docente_to_curso(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
        ]);

        $materia = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Programación I',
            'sigla' => 'SIS-101',
            'nivel' => 1,
            'nota_minima_aprobacion' => 51,
        ]);

        $curso = Curso::create([
            'materia_id' => $materia->id,
            'gestion' => '2026-I',
            'paralelo' => 'B',
            'cupo_maximo' => 50,
            'docente_id' => null,
        ]);

        $docente = User::factory()->create();
        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->postJson("/cursos/{$curso->id}/asignar-docente", [
            'docente_id' => $docente->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals($docente->id, $curso->fresh()->docente_id);
    }
}
