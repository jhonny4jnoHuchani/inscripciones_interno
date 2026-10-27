<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdmisionController extends Controller
{
    /**
     * Módulo principal de Admisiones, Postulantes y Kardex Manual.
     */
    public function index(Request $request): Response
    {
        $carreraId = $request->query('carrera_id') ?? Carrera::first()?->id;
        $carreras = Carrera::select('id', 'codigo', 'nombre')->get();

        // 1. Obtener postulantes y estudiantes
        $query = User::with(['roles', 'carrera', 'inscripciones.curso.materia'])
            ->where(function ($q) {
                $q->whereNotNull('estado_admision')
                  ->orWhereHas('roles', fn ($r) => $r->where('name', 'Estudiante'));
            });

        if ($carreraId) {
            $query->where('carrera_id', $carreraId);
        }

        if ($request->filled('estado')) {
            $query->where('estado_admision', $request->query('estado'));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->query('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $postulantes = $query->orderByDesc('id')->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'ci' => $u->ci,
                'email' => $u->email,
                'carrera' => $u->carrera?->nombre,
                'modalidad_admision' => $u->modalidad_admision ?? 'Examen de Admisión',
                'nota_admision' => $u->nota_admision !== null ? (float) $u->nota_admision : null,
                'estado_admision' => $u->estado_admision ?? 'Pendiente',
                'fecha_admision' => $u->fecha_admision?->format('d/m/Y'),
                'roles' => $u->roles->pluck('name'),
                'materias_inscritas_count' => $u->inscripciones->count(),
                'inscripciones' => $u->inscripciones->map(function ($ins) {
                    return [
                        'id' => $ins->id,
                        'sigla' => $ins->curso?->materia?->sigla,
                        'materia' => $ins->curso?->materia?->nombre,
                        'paralelo' => $ins->curso?->paralelo,
                        'gestion' => $ins->curso?->gestion,
                        'tipo_registro' => $ins->tipo_registro,
                        'estado' => $ins->estado,
                    ];
                }),
            ];
        });

        // 2. Métricas de Admisión
        $totalPostulantes = User::whereNotNull('estado_admision')->count();
        $admitidosCount = User::where('estado_admision', 'Admitido')->count();
        $noAdmitidosCount = User::where('estado_admision', 'No Admitido')->count();
        $pendientesCount = User::where('estado_admision', 'Pendiente')->count();

        // 3. Cursos disponibles para 1er Semestre (Nivel 1) y gestión activa
        $gestionActual = Curso::orderBy('gestion', 'desc')->value('gestion') ?? '2026-I';

        $cursosPrimerSemestre = Curso::with(['materia', 'docente:id,name'])
            ->withCount('inscripciones')
            ->where('gestion', $gestionActual)
            ->whereHas('materia', function ($q) use ($carreraId) {
                $q->where('nivel', 1);
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

        // 4. Roles y Permisos (con su descripción)
        $permisos = Permission::select('id', 'name', 'descripcion')->get();
        $roles = Role::with('permissions:id,name')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name'),
                'permissions_count' => $r->permissions->count(),
            ];
        });

        return Inertia::render('Admisiones/Index', [
            'postulantes' => $postulantes,
            'carreras' => $carreras,
            'carreraActivaId' => (int) $carreraId,
            'gestionActual' => $gestionActual,
            'cursosPrimerSemestre' => $cursosPrimerSemestre,
            'permisos' => $permisos,
            'roles' => $roles,
            'metricas' => [
                'total' => $totalPostulantes,
                'admitidos' => $admitidosCount,
                'no_admitidos' => $noAdmitidosCount,
                'pendientes' => $pendientesCount,
                'tasa_aprobacion' => ($totalPostulantes > 0) ? round(($admitidosCount / $totalPostulantes) * 100, 1) : 0,
            ],
            'filtros' => [
                'estado' => $request->query('estado', ''),
                'buscar' => $request->query('buscar', ''),
            ],
        ]);
    }

    /**
     * Registra un nuevo postulante.
     */
    public function storePostulante(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:users,ci',
            'email' => 'required|string|email|max:255|unique:users,email',
            'carrera_id' => 'required|exists:carreras,id',
            'modalidad_admision' => 'required|string|in:Examen de Admisión,Excelencia Académica,Traspaso,Otro',
            'nota_admision' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'ci' => $validated['ci'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['ci']), // CI por defecto como contraseña
            'carrera_id' => $validated['carrera_id'],
            'modalidad_admision' => $validated['modalidad_admision'],
            'estado_admision' => 'Pendiente',
        ]);

        // Si se proporciona nota o modalidad especial de excelencia
        if ($validated['modalidad_admision'] === 'Excelencia Académica' || $request->filled('nota_admision')) {
            $user->evaluarAdmision(
                $request->filled('nota_admision') ? (float) $validated['nota_admision'] : 100.0,
                $validated['modalidad_admision']
            );
        }

        return back()->with('success', "Postulante {$user->name} registrado exitosamente.");
    }

    /**
     * Asienta la nota del examen de admisión y evalúa aprobación (mínimo 61 puntos).
     */
    public function registrarNota(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nota_admision' => 'required|numeric|min:0|max:100',
            'modalidad_admision' => 'nullable|string',
        ]);

        $modalidad = $validated['modalidad_admision'] ?? $user->modalidad_admision ?? 'Examen de Admisión';
        $admitido = $user->evaluarAdmision((float) $validated['nota_admision'], $modalidad);

        if ($admitido) {
            return back()->with('success', "¡Postulante Admitido! Nota registrada: {$user->nota_admision}/100. Se habilitó el rol Estudiante.");
        }

        return back()->with('info', "Nota registrada: {$user->nota_admision}/100. El postulante No fue admitido (se requiere nota >= 61).");
    }

    /**
     * Inscripción manual por Kardex para estudiantes formalmente admitidos.
     */
    public function inscribirManual(Request $request, User $user): RedirectResponse
    {
        // 1. Regla de negocio crítica: debe estar Admitido
        if (! $user->estaAdmitido()) {
            return back()->withErrors([
                'error' => "El estudiante no está formalmente admitido (nota actual: {$user->nota_admision}, estado: {$user->estado_admision}). Solo estudiantes con nota >= 61 o Excelencia Académica pueden inscribirse.",
            ]);
        }

        $validated = $request->validate([
            'curso_ids' => 'required|array|min:1',
            'curso_ids.*' => 'required|exists:cursos,id',
        ]);

        $inscritos = 0;
        $errores = [];

        foreach ($validated['curso_ids'] as $cursoId) {
            $curso = Curso::with('materia')->findOrFail($cursoId);

            // Validar cupo
            $ocupacion = $curso->inscripciones()->count();
            if ($ocupacion >= $curso->cupo_maximo) {
                $errores[] = "Cupo lleno para {$curso->materia->nombre} (Paralelo {$curso->paralelo}).";
                continue;
            }

            // Validar pre-requisitos
            if (! $user->cumpleRequisitosPara($curso->materia)) {
                $errores[] = "No cumple los pre-requisitos exigidos para {$curso->materia->nombre}.";
                continue;
            }

            // Validar si ya está en ese curso exacto
            $yaInscritoEnEsteCurso = $user->inscripciones()->where('curso_id', $curso->id)->exists();
            if ($yaInscritoEnEsteCurso) {
                continue;
            }

            // Validar que no se inscriba dos veces a la misma materia en paralelos distintos para el mismo semestre
            $yaInscritoEnMateria = $user->inscripciones()
                ->whereHas('curso', function ($q) use ($curso) {
                    $q->where('materia_id', $curso->materia_id)
                      ->where('gestion', $curso->gestion);
                })
                ->exists();

            if ($yaInscritoEnMateria) {
                $errores[] = "El estudiante ya cuenta con un paralelo asignado para {$curso->materia->nombre} en {$curso->gestion}.";
                continue;
            }

            // Realizar la inscripción como Kardex Manual
            Inscripcion::create([
                'estudiante_id' => $user->id,
                'curso_id' => $curso->id,
                'tipo_registro' => 'Kardex Manual',
                'estado' => 'En Curso',
            ]);

            $inscritos++;
        }

        if (! empty($errores)) {
            return back()->with('warning', "Se inscribieron {$inscritos} materia(s). Observaciones: " . implode(' ', $errores));
        }

        return back()->with('success', "Inscripción manual por Kardex completada con éxito ({$inscritos} materias asignadas).");
    }

    /**
     * Eliminar la inscripción de una materia específica (desasignación manual por Kardex).
     */
    public function desinscribirMateria(User $user, Inscripcion $inscripcion): RedirectResponse
    {
        if ($inscripcion->estudiante_id !== $user->id) {
            abort(403, 'Inscripción no corresponde al estudiante.');
        }

        $inscripcion->delete();

        return back()->with('success', 'Materia desinscrita correctamente.');
    }
}
