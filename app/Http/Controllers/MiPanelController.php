<?php

namespace App\Http\Controllers;

use App\Models\PeriodoEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MiPanelController extends Controller
{
    /**
     * Portal exclusivo del estudiante para consulta de materias inscritas, notas parciales y avance.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $gestionActual = $request->query('gestion', '2026-I');

        $user->load([
            'carrera:id,codigo,nombre,total_niveles',
            'inscripciones.curso.materia:id,codigo,sigla,nombre,nivel',
            'inscripciones.curso.docente:id,name,email',
            'inscripciones.calificacionesParciales.periodoEvaluacion',
        ]);

        $periodos = PeriodoEvaluacion::where('gestion', $gestionActual)
            ->orderBy('numero_parcial')
            ->get();

        $materiasActuales = $user->inscripciones
            ->filter(fn ($i) => $i->curso?->gestion === $gestionActual)
            ->map(function ($ins) {
                $calificaciones = [];
                foreach ($ins->calificacionesParciales as $cp) {
                    $calificaciones[$cp->periodo_evaluacion_id] = (float) $cp->nota;
                }

                return [
                    'id' => $ins->id,
                    'materia_sigla' => $ins->curso?->materia?->sigla,
                    'materia_nombre' => $ins->curso?->materia?->nombre,
                    'nivel' => $ins->curso?->materia?->nivel,
                    'paralelo' => $ins->curso?->paralelo,
                    'docente' => $ins->curso?->docente?->name ?? 'Por Asignar',
                    'nota_final' => $ins->nota_final !== null ? (float) $ins->nota_final : null,
                    'estado' => $ins->estado,
                    'calificaciones' => $calificaciones,
                ];
            })
            ->values();

        $materiasHistorial = $user->inscripciones
            ->filter(fn ($i) => $i->curso?->gestion !== $gestionActual || $i->estado !== 'En Curso')
            ->map(function ($ins) {
                return [
                    'id' => $ins->id,
                    'gestion' => $ins->curso?->gestion,
                    'materia_sigla' => $ins->curso?->materia?->sigla,
                    'materia_nombre' => $ins->curso?->materia?->nombre,
                    'nivel' => $ins->curso?->materia?->nivel,
                    'nota_final' => $ins->nota_final !== null ? (float) $ins->nota_final : null,
                    'estado' => $ins->estado,
                ];
            })
            ->values();

        $aprobadas = $user->inscripciones->where('estado', 'Aprobado');
        $promedio = $aprobadas->count() > 0 ? round($aprobadas->avg('nota_final'), 1) : 0;

        return Inertia::render('MiPanel', [
            'estudiante' => [
                'name' => $user->name,
                'ci' => $user->ci,
                'email' => $user->email,
                'carrera' => $user->carrera?->nombre,
                'codigo_carrera' => $user->carrera?->codigo,
                'total_niveles' => $user->carrera?->total_niveles ?? 10,
                'aprobadas_count' => $aprobadas->count(),
                'promedio_general' => $promedio,
            ],
            'materiasActuales' => $materiasActuales,
            'materiasHistorial' => $materiasHistorial,
            'periodos' => $periodos,
            'gestionActual' => $gestionActual,
        ]);
    }
}
