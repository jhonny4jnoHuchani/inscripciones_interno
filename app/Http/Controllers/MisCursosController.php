<?php

namespace App\Http\Controllers;

use App\Models\CalificacionParcial;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\PeriodoEvaluacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MisCursosController extends Controller
{
    /**
     * Muestra el panel docente con los cursos asignados y las listas de estudiantes por parcial.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $gestionActual = $request->query('gestion', '2026-I');

        // Cursos asignados al docente autenticado
        $cursos = Curso::with([
            'materia:id,sigla,nombre,nivel,carrera_id',
            'materia.carrera:id,codigo,nombre',
            'inscripciones.estudiante:id,name,ci,email',
            'inscripciones.calificacionesParciales',
        ])
            ->where('docente_id', $user->id)
            ->where('gestion', $gestionActual)
            ->orderBy('id')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'materia_sigla' => $c->materia?->sigla,
                    'materia_nombre' => $c->materia?->nombre,
                    'carrera' => $c->materia?->carrera?->nombre,
                    'nivel' => $c->materia?->nivel,
                    'paralelo' => $c->paralelo,
                    'gestion' => $c->gestion,
                    'cupo_maximo' => $c->cupo_maximo,
                    'inscritos_count' => $c->inscripciones->count(),
                    'estudiantes' => $c->inscripciones->map(function ($ins) {
                        $calificaciones = [];
                        foreach ($ins->calificacionesParciales as $cp) {
                            $calificaciones[$cp->periodo_evaluacion_id] = (float) $cp->nota;
                        }

                        return [
                            'inscripcion_id' => $ins->id,
                            'estudiante_id' => $ins->estudiante?->id,
                            'name' => $ins->estudiante?->name,
                            'ci' => $ins->estudiante?->ci,
                            'email' => $ins->estudiante?->email,
                            'nota_final' => $ins->nota_final !== null ? (float) $ins->nota_final : null,
                            'estado' => $ins->estado,
                            'calificaciones' => $calificaciones,
                        ];
                    }),
                ];
            });

        // Periodos de evaluación para la gestión activa
        $periodos = PeriodoEvaluacion::where('gestion', $gestionActual)
            ->orderBy('numero_parcial')
            ->get();

        return Inertia::render('MisCursos', [
            'cursos' => $cursos,
            'periodos' => $periodos,
            'gestionActual' => $gestionActual,
        ]);
    }

    /**
     * Registra o actualiza las notas de un ciclo/parcial específico para un curso.
     * Valida estrictamente que las fechas del periodo estén actualmente habilitadas.
     */
    public function guardarNotas(Request $request, Curso $curso): RedirectResponse
    {
        $user = Auth::user();

        // Validar que el curso pertenezca al docente o sea administrador
        if ($curso->docente_id !== $user->id && !$user->hasRole('Administrador')) {
            abort(403, 'No tiene permiso para asentar calificaciones en este curso.');
        }

        $validated = $request->validate([
            'periodo_evaluacion_id' => 'required|exists:periodos_evaluacion,id',
            'notas' => 'required|array',
            'notas.*.inscripcion_id' => 'required|exists:inscripciones,id',
            'notas.*.nota' => 'required|numeric|min:0|max:100',
        ]);

        $periodo = PeriodoEvaluacion::findOrFail($validated['periodo_evaluacion_id']);

        // Validación estricta de fechas de habilitación para docentes
        if (!$periodo->esta_habilitado && !$user->hasRole('Administrador')) {
            return back()->withErrors([
                'error' => "El ciclo '{$periodo->nombre}' no está habilitado para el registro de notas. {$periodo->estado_texto}",
            ]);
        }

        foreach ($validated['notas'] as $item) {
            $inscripcion = Inscripcion::where('id', $item['inscripcion_id'])
                ->where('curso_id', $curso->id)
                ->first();

            if (!$inscripcion) {
                continue;
            }

            CalificacionParcial::updateOrCreate(
                [
                    'inscripcion_id' => $inscripcion->id,
                    'periodo_evaluacion_id' => $periodo->id,
                ],
                [
                    'nota' => $item['nota'],
                ]
            );

            // Recalcula la nota final y estado (Aprobado/Reprobado)
            $inscripcion->recalcularNotaFinal();
        }

        return back()->with('success', "Se guardaron satisfactoriamente las notas para {$periodo->nombre}.");
    }
}
