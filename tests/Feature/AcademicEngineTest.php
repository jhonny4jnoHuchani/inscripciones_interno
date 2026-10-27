<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_materia_can_have_pre_requisitos(): void
    {
        $calculo1 = Materia::create([
            'nombre' => 'Cálculo I',
            'sigla' => 'MAT-101',
            'nota_minima_aprobacion' => 51,
        ]);

        $calculo2 = Materia::create([
            'nombre' => 'Cálculo II',
            'sigla' => 'MAT-102',
            'nota_minima_aprobacion' => 51,
        ]);

        $calculo2->requisitos()->attach($calculo1->id);

        $this->assertTrue($calculo2->requisitos->contains($calculo1));
        $this->assertTrue($calculo1->materiasQueLaExigen->contains($calculo2));
    }

    public function test_user_ha_aprobado_materia(): void
    {
        $docente = User::factory()->create();
        $estudiante = User::factory()->create();

        $materia = Materia::create([
            'nombre' => 'Física I',
            'sigla' => 'FIS-101',
            'nota_minima_aprobacion' => 51,
        ]);

        $curso = Curso::create([
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
            'gestion' => '2026-I',
            'fecha_inicio_calificacion' => now()->subDays(5),
            'fecha_fin_calificacion' => now()->addDays(5),
        ]);

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudiante->id,
            'curso_id' => $curso->id,
            'estado' => 'En Curso',
            'tipo_registro' => 'Auto-Inscripción',
        ]);

        $this->assertFalse($estudiante->haAprobadoMateria($materia->id));

        $inscripcion->update(['estado' => 'Aprobado', 'nota_final' => 75]);

        $this->assertTrue($estudiante->haAprobadoMateria($materia->id));
    }

    public function test_user_cumple_requisitos_para_materia(): void
    {
        $docente = User::factory()->create();
        $estudiante = User::factory()->create();

        $prog1 = Materia::create([
            'nombre' => 'Programación I',
            'sigla' => 'SIS-101',
            'nota_minima_aprobacion' => 51,
        ]);

        $prog2 = Materia::create([
            'nombre' => 'Programación II',
            'sigla' => 'SIS-102',
            'nota_minima_aprobacion' => 51,
        ]);

        $prog2->requisitos()->attach($prog1->id);

        // Sin requisitos previos en Prog1, cumple directamente
        $this->assertTrue($estudiante->cumpleRequisitosPara($prog1));

        // Para Prog2, aún no la tiene aprobada
        $this->assertFalse($estudiante->cumpleRequisitosPara($prog2));

        // Inscribe y aprueba Prog1
        $cursoProg1 = Curso::create([
            'materia_id' => $prog1->id,
            'docente_id' => $docente->id,
            'gestion' => '2026-I',
            'fecha_inicio_calificacion' => now()->subDays(5),
            'fecha_fin_calificacion' => now()->addDays(5),
        ]);

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $estudiante->id,
            'curso_id' => $cursoProg1->id,
            'estado' => 'En Curso',
            'tipo_registro' => 'Auto-Inscripción',
        ]);

        $inscripcion->asentarNota(65);

        // Ahora debe cumplir requisitos para Prog2
        $this->assertTrue($estudiante->cumpleRequisitosPara($prog2));
    }

    public function test_curso_esta_en_ventana_de_calificacion(): void
    {
        $docente = User::factory()->create();
        $materia = Materia::create([
            'nombre' => 'Álgebra Lineal',
            'sigla' => 'MAT-103',
        ]);

        $cursoAbierto = Curso::create([
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
            'gestion' => '2026-I',
            'fecha_inicio_calificacion' => now()->subDay(),
            'fecha_fin_calificacion' => now()->addDay(),
        ]);

        $cursoCerrado = Curso::create([
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
            'gestion' => '2025-II',
            'fecha_inicio_calificacion' => now()->subMonths(3),
            'fecha_fin_calificacion' => now()->subMonths(2),
        ]);

        $this->assertTrue($cursoAbierto->estaEnVentanaDeCalificacion());
        $this->assertFalse($cursoCerrado->estaEnVentanaDeCalificacion());
    }

    public function test_inscripcion_asentar_nota_evalua_aprobado_y_reprobado(): void
    {
        $docente = User::factory()->create();
        $estudianteA = User::factory()->create();
        $estudianteB = User::factory()->create();

        // Materia con nota mínima personalizada (61)
        $materia = Materia::create([
            'nombre' => 'Taller de Grado',
            'sigla' => 'TG-501',
            'nota_minima_aprobacion' => 61,
        ]);

        $curso = Curso::create([
            'materia_id' => $materia->id,
            'docente_id' => $docente->id,
            'gestion' => '2026-I',
            'fecha_inicio_calificacion' => now()->subDays(2),
            'fecha_fin_calificacion' => now()->addDays(2),
        ]);

        $inscripcionA = Inscripcion::create([
            'estudiante_id' => $estudianteA->id,
            'curso_id' => $curso->id,
            'estado' => 'En Curso',
            'tipo_registro' => 'Auto-Inscripción',
        ]);

        $inscripcionB = Inscripcion::create([
            'estudiante_id' => $estudianteB->id,
            'curso_id' => $curso->id,
            'estado' => 'En Curso',
            'tipo_registro' => 'Kardex Manual',
        ]);

        // Nota 60 con mínima 61 -> Reprobado
        $inscripcionA->asentarNota(60);
        $this->assertEquals('Reprobado', $inscripcionA->estado);
        $this->assertEquals(60, $inscripcionA->nota_final);

        // Nota 61 con mínima 61 -> Aprobado
        $inscripcionB->asentarNota(61);
        $this->assertEquals('Aprobado', $inscripcionB->estado);
        $this->assertEquals(61, $inscripcionB->nota_final);
    }
}
