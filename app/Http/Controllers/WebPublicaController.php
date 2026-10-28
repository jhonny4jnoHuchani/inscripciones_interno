<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\PortalAutoridad;
use App\Models\PortalInstitucion;
use App\Models\PortalPortada;
use App\Models\PortalPublicacion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class WebPublicaController extends Controller
{
    /**
     * Muestra la página web institucional (Landing Page).
     */
    public function index(): Response
    {
        $institucion = Schema::hasTable('portal_institucion') ? PortalInstitucion::first() : null;
        $portadas = Schema::hasTable('portal_portadas') ? PortalPortada::where('activo', true)->orderBy('orden')->get() : [];
        $autoridades = Schema::hasTable('portal_autoridades') ? PortalAutoridad::where('activo', true)->orderBy('orden')->get() : [];
        $publicaciones = Schema::hasTable('portal_publicaciones') ? PortalPublicacion::where('activo', true)->orderBy('fecha', 'desc')->get() : [];
        $carreras = Schema::hasTable('carreras') ? Carrera::withCount('materias')->orderBy('nombre')->get() : [];

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'institucion' => $institucion,
            'portadas' => $portadas,
            'autoridades' => $autoridades,
            'publicaciones' => $publicaciones,
            'carreras' => $carreras,
        ]);
    }

    /**
     * Consulta pública del estado de postulación al examen mediante número de CI.
     */
    public function consultarPostulacion(Request $request): JsonResponse
    {
        $request->validate([
            'ci' => 'required|string|max:20',
        ]);

        $postulante = User::with('carrera')
            ->where('ci', $request->ci)
            ->whereNotNull('estado_admision')
            ->first();

        if (! $postulante) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'No se encontró ninguna postulación registrada con la cédula de identidad proporcionada.',
            ]);
        }

        return response()->json([
            'encontrado' => true,
            'nombre' => $postulante->name,
            'ci' => $postulante->ci,
            'carrera' => $postulante->carrera?->nombre ?? 'Sin Carrera Asignada',
            'modalidad' => $postulante->modalidad_admision ?? 'Examen de Admisión',
            'estado_admision' => $postulante->estado_admision,
            'nota_admision' => $postulante->nota_admision !== null ? (float) $postulante->nota_admision : null,
            'fecha_admision' => $postulante->fecha_admision?->format('d/m/Y'),
        ]);
    }
}
