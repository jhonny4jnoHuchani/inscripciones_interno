<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KardexController extends Controller
{
    /**
     * Módulo exclusivo de Kardex Académico para gestión de Estudiantes regulares.
     */
    public function index(Request $request): Response
    {
        $carreraId = $request->query('carrera_id') ?? Carrera::first()?->id;
        $carreras = Carrera::select('id', 'codigo', 'nombre')->get();
        $gestionActual = $request->query('gestion', '2026-I');

        // Estudiantes regulares (con rol Estudiante y formalmente admitidos)
        $query = User::role('Estudiante')
            ->with(['carrera', 'inscripciones.curso.materia'])
            ->where(function ($q) {
                $q->where('estado_admision', 'Admitido')
                  ->orWhereNull('estado_admision'); // Para compatibilidad con estudiantes históricos
            });

        if ($carreraId) {
            $query->where('carrera_id', $carreraId);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->query('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $estudiantes = $query->orderBy('name')->get()->map(function ($u) use ($gestionActual) {
            $materiasAprobadas = $u->inscripciones->where('estado', 'Aprobado');
            $materiasEnCurso = $u->inscripciones->filter(fn ($i) => $i->curso?->gestion === $gestionActual && $i->estado === 'En Curso');
            $promedio = $materiasAprobadas->count() > 0
                ? round($materiasAprobadas->avg('nota_final'), 1)
                : 0;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'ci' => $u->ci,
                'email' => $u->email,
                'carrera' => $u->carrera?->nombre,
                'carrera_id' => $u->carrera_id,
                'aprobadas_count' => $materiasAprobadas->count(),
                'en_curso_count' => $materiasEnCurso->count(),
                'promedio_general' => $promedio,
                'inscripciones' => $u->inscripciones->map(function ($ins) {
                    return [
                        'id' => $ins->id,
                        'curso_id' => $ins->curso_id,
                        'sigla' => $ins->curso?->materia?->sigla,
                        'materia' => $ins->curso?->materia?->nombre,
                        'nivel' => $ins->curso?->materia?->nivel,
                        'paralelo' => $ins->curso?->paralelo,
                        'gestion' => $ins->curso?->gestion,
                        'nota_final' => $ins->nota_final !== null ? (float) $ins->nota_final : null,
                        'estado' => $ins->estado,
                        'tipo_registro' => $ins->tipo_registro,
                    ];
                }),
            ];
        });

        // Cursos disponibles para inscripción en la gestión activa
        $cursosDisponibles = Curso::with(['materia', 'docente:id,name'])
            ->withCount('inscripciones')
            ->where('gestion', $gestionActual)
            ->whereHas('materia', function ($q) use ($carreraId) {
                if ($carreraId) {
                    $q->where('carrera_id', $carreraId);
                }
            })
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'materia_id' => $c->materia_id,
                    'materia_nombre' => $c->materia->nombre,
                    'materia_sigla' => $c->materia->sigla,
                    'nivel' => $c->materia->nivel,
                    'paralelo' => $c->paralelo,
                    'gestion' => $c->gestion,
                    'cupo_maximo' => $c->cupo_maximo,
                    'inscripciones_count' => $c->inscripciones_count,
                    'cupos_disponibles' => max(0, $c->cupo_maximo - $c->inscripciones_count),
                    'docente' => $c->docente?->name ?? 'Sin Asignar',
                ];
            });

        return Inertia::render('Kardex/Index', [
            'estudiantes' => $estudiantes,
            'carreras' => $carreras,
            'carreraActivaId' => (int) $carreraId,
            'gestionActual' => $gestionActual,
            'cursosDisponibles' => $cursosDisponibles,
            'filtros' => [
                'buscar' => $request->query('buscar', ''),
            ],
        ]);
    }

    /**
     * Inscribe manualmente una materia a un estudiante regular validando prerrequisitos y cupo.
     */
    public function inscribirManual(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
        ]);

        $curso = Curso::with('materia.requisitos')->findOrFail($validated['curso_id']);

        // 1. Validar que no esté ya inscrito en este curso
        $yaInscritoEnEsteCurso = $user->inscripciones()->where('curso_id', $curso->id)->exists();
        if ($yaInscritoEnEsteCurso) {
            return back()->withErrors(['error' => "El estudiante ya está inscrito en {$curso->materia->nombre} (Paralelo {$curso->paralelo})."]);
        }

        // 2. Validar que no esté inscrito en la misma materia en otro paralelo en la misma gestión
        $yaInscritoEnMateria = $user->inscripciones()
            ->whereHas('curso', function ($q) use ($curso) {
                $q->where('materia_id', $curso->materia_id)
                  ->where('gestion', $curso->gestion);
            })
            ->exists();

        if ($yaInscritoEnMateria) {
            return back()->withErrors(['error' => "El estudiante ya está inscrito en {$curso->materia->nombre} en otro paralelo para la gestión {$curso->gestion}."]);
        }

        // 3. Validar cupo
        $ocupacion = $curso->inscripciones()->count();
        if ($ocupacion >= $curso->cupo_maximo) {
            return back()->withErrors(['error' => "Cupo lleno para {$curso->materia->nombre} (Paralelo {$curso->paralelo})."]);
        }

        // 4. Validar pre-requisitos
        if (! $user->cumpleRequisitosPara($curso->materia)) {
            $requisitosFaltantes = $curso->materia->requisitos
                ->reject(fn ($req) => $user->haAprobadoMateria($req))
                ->pluck('sigla')
                ->implode(', ');

            return back()->withErrors(['error' => "No cumple los pre-requisitos obligatorios ({$requisitosFaltantes}) para inscribir {$curso->materia->nombre}."]);
        }

        // 5. Registrar inscripción
        Inscripcion::create([
            'estudiante_id' => $user->id,
            'curso_id' => $curso->id,
            'estado' => 'En Curso',
            'tipo_registro' => 'Kardex Manual',
        ]);

        return back()->with('success', "Se inscribió manualmente a {$user->name} en {$curso->materia->nombre} (Paralelo {$curso->paralelo}).");
    }

    /**
     * Retira / desinscribe una materia del Kardex del estudiante.
     */
    public function desinscribirMateria(User $user, Inscripcion $inscripcion): RedirectResponse
    {
        if ($inscripcion->estudiante_id !== $user->id) {
            abort(403, 'La inscripción no corresponde al estudiante.');
        }

        $materiaNombre = $inscripcion->curso?->materia?->nombre ?? 'Materia';
        $inscripcion->delete();

        return back()->with('success', "Se retiró la materia {$materiaNombre} del historial del estudiante {$user->name}.");
    }
}
