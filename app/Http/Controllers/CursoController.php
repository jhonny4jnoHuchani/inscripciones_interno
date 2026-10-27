<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
{
    /**
     * Pantalla interactiva para gestionar cursos, paralelos y asignación docente.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Determinar carrera activa
        $carreraId = $request->query('carrera_id')
            ?? $user?->carrera_id
            ?? $user?->carreraDirigida?->id
            ?? Carrera::first()?->id;

        $carreraActiva = Carrera::with(['materias' => function ($q) {
            $q->orderBy('nivel')->orderBy('sigla');
        }])->find($carreraId) ?? Carrera::first();

        // 2. Determinar gestión activa (semestre)
        $gestionesRegistradas = Curso::select('gestion')
            ->distinct()
            ->orderBy('gestion', 'desc')
            ->pluck('gestion')
            ->toArray();

        if (empty($gestionesRegistradas)) {
            $gestionesRegistradas = ['2026-I', '2026-II'];
        }

        $gestionActual = $request->query('gestion') ?? $gestionesRegistradas[0] ?? '2026-I';

        // 3. Obtener cursos / paralelos abiertos en la gestión para esta carrera
        $cursos = Curso::with(['materia', 'docente:id,name,email'])
            ->withCount('inscripciones')
            ->where('gestion', $gestionActual)
            ->whereHas('materia', function ($q) use ($carreraActiva) {
                $q->where('carrera_id', $carreraActiva?->id);
            })
            ->get();

        // 4. Lista de todos los docentes disponibles para asignación
        try {
            $docentes = User::role('Docente')
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            $docentes = collect();
        }

        $carreras = Carrera::select('id', 'nombre', 'codigo')->get();

        return Inertia::render('Cursos/Index', [
            'carreraActiva' => $carreraActiva,
            'carreras' => $carreras,
            'gestionActual' => $gestionActual,
            'gestionesDisponibles' => $gestionesRegistradas,
            'cursos' => $cursos,
            'docentes' => $docentes,
        ]);
    }

    /**
     * Abre un nuevo paralelo para una materia con cupo y docente opcional.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'gestion' => 'required|string|max:20',
            'paralelo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('cursos')->where(function ($query) use ($request) {
                    return $query->where('materia_id', $request->materia_id)
                        ->where('gestion', $request->gestion)
                        ->where('paralelo', strtoupper(trim($request->paralelo)));
                }),
            ],
            'cupo_maximo' => 'required|integer|min:1|max:500',
            'docente_id' => 'nullable|exists:users,id',
            'fecha_inicio_calificacion' => 'nullable|date',
            'fecha_fin_calificacion' => 'nullable|date|after_or_equal:fecha_inicio_calificacion',
        ], [
            'paralelo.unique' => 'Ya existe este paralelo para la materia en la gestión seleccionada.',
        ]);

        $validated['paralelo'] = strtoupper(trim($validated['paralelo']));

        // Si no se asignan fechas de calificación, se establece por defecto un rango de 6 meses
        if (empty($validated['fecha_inicio_calificacion'])) {
            $validated['fecha_inicio_calificacion'] = now();
            $validated['fecha_fin_calificacion'] = now()->addMonths(5);
        }

        Curso::create($validated);

        return back()->with('success', 'Paralelo habilitado exitosamente.');
    }

    /**
     * Modifica los datos del paralelo (cupo, fechas o docente).
     */
    public function update(Request $request, Curso $curso): RedirectResponse
    {
        $validated = $request->validate([
            'paralelo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('cursos')->ignore($curso->id)->where(function ($query) use ($curso) {
                    return $query->where('materia_id', $curso->materia_id)
                        ->where('gestion', $curso->gestion);
                }),
            ],
            'cupo_maximo' => 'required|integer|min:1|max:500',
            'docente_id' => 'nullable|exists:users,id',
            'fecha_inicio_calificacion' => 'nullable|date',
            'fecha_fin_calificacion' => 'nullable|date|after_or_equal:fecha_inicio_calificacion',
        ]);

        $validated['paralelo'] = strtoupper(trim($validated['paralelo']));

        $curso->update($validated);

        return back()->with('success', 'Paralelo actualizado.');
    }

    /**
     * Asignación rápida de docente a un curso específico vía Ajax.
     */
    public function asignarDocente(Request $request, Curso $curso): JsonResponse
    {
        $validated = $request->validate([
            'docente_id' => 'nullable|exists:users,id',
        ]);

        $curso->update(['docente_id' => $validated['docente_id']]);
        $curso->load('docente:id,name,email');

        return response()->json([
            'success' => true,
            'curso' => $curso,
            'message' => 'Docente asignado correctamente.',
        ]);
    }

    /**
     * Elimina el paralelo si no tiene estudiantes inscritos.
     */
    public function destroy(Curso $curso): RedirectResponse
    {
        if ($curso->inscripciones()->exists()) {
            return back()->withErrors([
                'error' => 'No se puede eliminar el paralelo porque ya cuenta con estudiantes inscritos.',
            ]);
        }

        $curso->delete();

        return back()->with('success', 'Paralelo eliminado.');
    }
}
