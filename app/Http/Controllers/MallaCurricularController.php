<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Materia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MallaCurricularController extends Controller
{
    /**
     * Muestra la pantalla interactiva de la Malla Curricular.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Si el usuario es director y tiene carrera asignada, la priorizamos; de lo contrario tomamos la solicitada o la primera disponible.
        $carreraId = $request->query('carrera_id')
            ?? $user?->carrera_id
            ?? $user?->carreraDirigida?->id
            ?? Carrera::first()?->id;

        $carrera = Carrera::with(['materias.requisitos'])->find($carreraId)
            ?? Carrera::first();

        $todasCarreras = Carrera::select('id', 'nombre', 'codigo', 'total_niveles')->get();

        return Inertia::render('MallaCurricular/Index', [
            'carreraActiva' => $carrera,
            'carreras' => $todasCarreras,
        ]);
    }

    /**
     * Crea una nueva materia en un nivel específico de la carrera.
     */
    public function storeMateria(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => 'required|string|max:255',
            'sigla' => 'required|string|max:50|unique:materias,sigla',
            'nivel' => 'required|integer|min:1|max:12',
            'nota_minima_aprobacion' => 'required|integer|min:1|max:100',
        ]);

        Materia::create($validated);

        return back()->with('success', 'Materia creada exitosamente.');
    }

    /**
     * Actualiza los datos de una materia existente.
     */
    public function updateMateria(Request $request, Materia $materia): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'sigla' => 'required|string|max:50|unique:materias,sigla,' . $materia->id,
            'nivel' => 'required|integer|min:1|max:12',
            'nota_minima_aprobacion' => 'required|integer|min:1|max:100',
        ]);

        $materia->update($validated);

        return back()->with('success', 'Materia actualizada.');
    }

    /**
     * Elimina una materia de la malla curricular.
     */
    public function destroyMateria(Materia $materia): RedirectResponse
    {
        $materia->delete();

        return back()->with('success', 'Materia eliminada de la malla.');
    }

    /**
     * Conecta o desconecta un pre-requisito (flecha dinámica).
     */
    public function toggleRequisito(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'requisito_id' => 'required|exists:materias,id|different:materia_id',
        ]);

        $materia = Materia::findOrFail($validated['materia_id']);
        $requisitoId = $validated['requisito_id'];

        if ($materia->requisitos()->where('requisito_id', $requisitoId)->exists()) {
            $materia->requisitos()->detach($requisitoId);
            $action = 'detached';
        } else {
            $materia->requisitos()->attach($requisitoId);
            $action = 'attached';
        }

        // Retornamos la materia actualizada con todos sus requisitos
        $materiaActualizada = Materia::with('requisitos')->find($materia->id);

        return response()->json([
            'success' => true,
            'action' => $action,
            'materia' => $materiaActualizada,
        ]);
    }
}
