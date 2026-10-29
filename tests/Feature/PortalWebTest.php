<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\PortalAutoridad;
use App\Models\PortalInstitucion;
use App\Models\PortalPublicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Director']);
    }

    public function test_pagina_web_publica_carga_correctamente(): void
    {
        $carrera = Carrera::create(['nombre' => 'Ingeniería de Sistemas', 'codigo' => 'SIS']);

        PortalInstitucion::create([
            'nombre' => 'Facultad de Tecnología',
            'sigla' => 'FAC-TEC',
            'historia' => 'Historia de prueba',
            'mision' => 'Misión de prueba',
            'vision' => 'Visión de prueba',
        ]);

        $response = $this->get('/');
        $response->assertOk();
    }

    public function test_consulta_postulacion_por_ci(): void
    {
        $carrera = Carrera::create(['nombre' => 'Informática', 'codigo' => 'INF']);

        $postulante = User::create([
            'name' => 'Juan Aspirante',
            'ci' => '12345678',
            'email' => 'juan@aspirante.test',
            'password' => bcrypt('password'),
            'carrera_id' => $carrera->id,
            'estado_admision' => 'Pendiente',
            'modalidad_admision' => 'Examen de Admisión',
        ]);

        $response = $this->postJson('/consulta-postulacion', [
            'ci' => '12345678',
        ]);

        $response->assertOk();
        $response->assertJson([
            'encontrado' => true,
            'nombre' => 'Juan Aspirante',
            'estado_admision' => 'Pendiente',
        ]);
    }

    public function test_admin_puede_gestionar_publicaciones_y_autoridades(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $admin = User::factory()->create(['carrera_id' => $carrera->id]);
        $admin->assignRole('Administrador');

        // 1. Guardar nueva publicación
        $this->actingAs($admin)->post('/portal-web/publicaciones', [
            'titulo' => 'Convocatoria a Examen',
            'tipo' => 'Convocatoria',
            'descripcion' => 'Descripción de prueba',
            'autor' => 'Dirección',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('portal_publicaciones', ['titulo' => 'Convocatoria a Examen']);

        // 2. Guardar nueva autoridad
        $this->actingAs($admin)->post('/portal-web/autoridades', [
            'nombre' => 'Ing. Carlos Mendoza',
            'cargo' => 'Director de Carrera',
            'grado_academico' => 'M.Sc.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('portal_autoridades', ['nombre' => 'Ing. Carlos Mendoza']);
    }

    public function test_postulante_puede_registrarse_publicamente(): void
    {
        $carrera = Carrera::create(['nombre' => 'Telecomunicaciones', 'codigo' => 'TEL']);

        $response = $this->post('/postulacion', [
            'name' => 'Maria Aspirante',
            'ci' => '87654321',
            'email' => 'maria@aspirante.test',
            'carrera_id' => $carrera->id,
            'modalidad_admision' => 'Examen de Admisión',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'ci' => '87654321',
            'email' => 'maria@aspirante.test',
            'estado_admision' => 'Pendiente',
        ]);
    }

    public function test_admin_puede_crear_portada(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $admin = User::factory()->create(['carrera_id' => $carrera->id]);
        $admin->assignRole('Administrador');

        $this->actingAs($admin)->post('/portal-web/portadas', [
            'titulo' => 'Nueva Portada 2026',
            'subtitulo' => 'Subtítulo de prueba',
            'badge' => 'Nuevo',
            'link_texto' => 'Ver Más',
            'orden' => 1,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('portal_portadas', ['titulo' => 'Nueva Portada 2026']);
    }
}
