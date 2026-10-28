<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\PeriodoEvaluacion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConfiguracionAcademicaController extends Controller
{
    /**
     * Muestra la pantalla de configuración académica.
     */
    public function index(Request $request): Response
    {
        $gestionActual = $request->query('gestion', '2026-I');

        $carreras = Carrera::orderBy('nombre')->get();
        $periodos = PeriodoEvaluacion::where('gestion', $gestionActual)
            ->orderBy('numero_parcial')
            ->get();

        return Inertia::render('ConfiguracionAcademica/Index', [
            'carreras' => $carreras,
            'periodos' => $periodos,
            'gestionActual' => $gestionActual,
        ]);
    }

    /**
     * Actualiza la cantidad de niveles / semestres de una carrera.
     */
    public function updateCarreraNiveles(Request $request, Carrera $carrera): RedirectResponse
    {
        $validated = $request->validate([
            'total_niveles' => 'required|integer|min:1|max:14',
        ]);

        $carrera->update([
            'total_niveles' => $validated['total_niveles'],
        ]);

        return back()->with('success', "Se actualizó la carrera {$carrera->nombre} a {$carrera->total_niveles} niveles/semestres.");
    }

    /**
     * Guarda o actualiza un periodo de evaluación (parcial/ciclo).
     */
    public function storePeriodo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gestion' => 'required|string|max:20',
            'numero_parcial' => 'required|integer|min:1|max:10',
            'nombre' => 'required|string|max:100',
            'ponderacion' => 'required|numeric|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'activo' => 'boolean',
        ]);

        PeriodoEvaluacion::updateOrCreate(
            [
                'gestion' => $validated['gestion'],
                'numero_parcial' => $validated['numero_parcial'],
            ],
            $validated
        );

        return back()->with('success', 'Periodo de evaluación / ciclo guardado correctamente.');
    }

    /**
     * Actualiza las fechas de habilitación de un periodo existente.
     */
    public function updatePeriodo(Request $request, PeriodoEvaluacion $periodo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'ponderacion' => 'required|numeric|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'activo' => 'boolean',
        ]);

        $periodo->update($validated);

        return back()->with('success', 'Periodo de evaluación actualizado.');
    }

    /**
     * Elimina un periodo de evaluación si no está en uso crítico.
     */
    public function destroyPeriodo(PeriodoEvaluacion $periodo): RedirectResponse
    {
        $periodo->delete();

        return back()->with('success', 'Periodo de evaluación eliminado.');
    }
}
