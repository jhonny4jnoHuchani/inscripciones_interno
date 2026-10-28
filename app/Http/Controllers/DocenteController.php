<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class DocenteController extends Controller
{
    /**
     * Muestra el listado de docentes y el resumen de sus materias asignadas.
     */
    public function index(Request $request): Response
    {
        $carreraId = $request->query('carrera_id');
        $gestionActual = $request->query('gestion', '2026-I');

        $carreras = Carrera::select('id', 'codigo', 'nombre')->get();

        $query = User::role('Docente')
            ->with([
                'carrera:id,codigo,nombre',
                'cursosDictados' => function ($q) {
                    $q->with(['materia:id,codigo,sigla,nombre,nivel', 'inscripciones:id,curso_id'])
                      ->withCount('inscripciones')
                      ->orderBy('gestion', 'desc');
                },
            ]);

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

        $docentes = $query->orderBy('name')->get()->map(function ($d) use ($gestionActual) {
            $cursosGestionActual = $d->cursosDictados->where('gestion', $gestionActual);
            $totalEstudiantesGestion = $cursosGestionActual->sum('inscripciones_count');

            return [
                'id' => $d->id,
                'name' => $d->name,
                'ci' => $d->ci,
                'email' => $d->email,
                'carrera' => $d->carrera?->nombre,
                'carrera_id' => $d->carrera_id,
                'cursos_gestion_count' => $cursosGestionActual->count(),
                'total_estudiantes' => $totalEstudiantesGestion,
                'cursos' => $d->cursosDictados->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'materia_sigla' => $c->materia?->sigla,
                        'materia_nombre' => $c->materia?->nombre,
                        'nivel' => $c->materia?->nivel,
                        'paralelo' => $c->paralelo,
                        'gestion' => $c->gestion,
                        'cupo_maximo' => $c->cupo_maximo,
                        'inscritos_count' => $c->inscripciones_count,
                    ];
                })->values(),
            ];
        });

        return Inertia::render('Docentes/Index', [
            'docentes' => $docentes,
            'carreras' => $carreras,
            'carreraActivaId' => $carreraId ? (int) $carreraId : null,
            'gestionActual' => $gestionActual,
            'filtros' => [
                'buscar' => $request->query('buscar', ''),
            ],
        ]);
    }

    /**
     * Registra un nuevo docente en el sistema.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:users,ci',
            'email' => 'required|string|email|max:255|unique:users,email',
            'carrera_id' => 'required|exists:carreras,id',
            'password' => 'nullable|string|min:6',
        ]);

        $docente = User::create([
            'name' => $validated['name'],
            'ci' => $validated['ci'],
            'email' => $validated['email'],
            'carrera_id' => $validated['carrera_id'],
            'password' => Hash::make($validated['password'] ?? 'password'),
        ]);

        $docente->syncRoles(['Docente']);

        return back()->with('success', "Docente {$docente->name} registrado exitosamente.");
    }

    /**
     * Actualiza los datos de un docente.
     */
    public function update(Request $request, User $docente): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:users,ci,' . $docente->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $docente->id,
            'carrera_id' => 'required|exists:carreras,id',
            'password' => 'nullable|string|min:6',
        ]);

        $docente->update([
            'name' => $validated['name'],
            'ci' => $validated['ci'],
            'email' => $validated['email'],
            'carrera_id' => $validated['carrera_id'],
        ]);

        if (!empty($validated['password'])) {
            $docente->update(['password' => Hash::make($validated['password'])]);
        }

        return back()->with('success', "Datos del docente {$docente->name} actualizados.");
    }

    /**
     * Elimina un docente si no tiene cursos activos.
     */
    public function destroy(User $docente): RedirectResponse
    {
        if ($docente->cursosDictados()->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar el docente porque tiene cursos/paralelos asignados en el sistema.']);
        }

        $docente->delete();

        return back()->with('success', 'Docente eliminado correctamente.');
    }
}
