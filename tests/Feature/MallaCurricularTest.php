<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MallaCurricularTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_malla_curricular(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
            'total_niveles' => 9,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->get('/malla-curricular');

        $response->assertStatus(200);
    }

    public function test_can_create_materia_in_level(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
            'total_niveles' => 9,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $response = $this->actingAs($user)->post('/malla-curricular/materias', [
            'carrera_id' => $carrera->id,
            'nombre' => 'Arquitectura de Computadoras',
            'sigla' => 'SIS-204',
            'nivel' => 3,
            'nota_minima_aprobacion' => 51,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('materias', [
            'sigla' => 'SIS-204',
            'carrera_id' => $carrera->id,
            'nivel' => 3,
        ]);
    }

    public function test_can_toggle_prerequisite_arrow(): void
    {
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'SIS',
            'total_niveles' => 9,
        ]);

        $user = User::factory()->create(['carrera_id' => $carrera->id]);

        $materia1 = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Matemática Discreta',
            'sigla' => 'MAT-105',
            'nivel' => 1,
            'nota_minima_aprobacion' => 51,
        ]);

        $materia2 = Materia::create([
            'carrera_id' => $carrera->id,
            'nombre' => 'Teoría de Autómatas',
            'sigla' => 'SIS-205',
            'nivel' => 3,
            'nota_minima_aprobacion' => 51,
        ]);

        // 1. Conectar requisito
        $responseAttach = $this->actingAs($user)->postJson('/malla-curricular/toggle-requisito', [
            'materia_id' => $materia2->id,
            'requisito_id' => $materia1->id,
        ]);

        $responseAttach->assertJson(['success' => true, 'action' => 'attached']);
        $this->assertTrue($materia2->fresh()->requisitos->contains($materia1));

        // 2. Desconectar requisito
        $responseDetach = $this->actingAs($user)->postJson('/malla-curricular/toggle-requisito', [
            'materia_id' => $materia2->id,
            'requisito_id' => $materia1->id,
        ]);

        $responseDetach->assertJson(['success' => true, 'action' => 'detached']);
        $this->assertFalse($materia2->fresh()->requisitos->contains($materia1));
    }
}
