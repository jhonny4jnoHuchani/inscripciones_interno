<?php

namespace App\Http\Controllers;

use App\Models\PortalAutoridad;
use App\Models\PortalInstitucion;
use App\Models\PortalPortada;
use App\Models\PortalPublicacion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PortalWebAdminController extends Controller
{
    /**
     * Muestra el panel administrativo del portal web institucional.
     */
    public function index(): Response
    {
        $institucion = PortalInstitucion::first();
        $portadas = PortalPortada::orderBy('orden')->get();
        $autoridades = PortalAutoridad::orderBy('orden')->get();
        $publicaciones = PortalPublicacion::orderBy('fecha', 'desc')->get();

        return Inertia::render('PortalWeb/Index', [
            'institucion' => $institucion,
            'portadas' => $portadas,
            'autoridades' => $autoridades,
            'publicaciones' => $publicaciones,
        ]);
    }

    /**
     * Actualiza la información institucional (historia, misión, visión, etc.).
     */
    public function updateInstitucion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'sigla' => 'required|string|max:50',
            'facultad' => 'nullable|string|max:255',
            'historia' => 'nullable|string',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:100',
            'whatsapp' => 'nullable|string|max:50',
        ]);

        $institucion = PortalInstitucion::first();
        if ($institucion) {
            $institucion->update($validated);
        } else {
            PortalInstitucion::create($validated);
        }

        return back()->with('success', 'Información institucional actualizada.');
    }

    /**
     * Guarda una portada para el Hero Slider.
     */
    public function storePortada(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'nullable|string',
            'badge' => 'nullable|string|max:100',
            'link_url' => 'nullable|string|max:255',
            'link_texto' => 'nullable|string|max:100',
            'orden' => 'nullable|integer',
            'activo' => 'boolean',
            'imagen' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('portal/portadas', 'public');
        }

        $validated['orden'] = $validated['orden'] ?? ((PortalPortada::max('orden') ?? 0) + 1);
        $validated['activo'] = $validated['activo'] ?? true;

        PortalPortada::create($validated);

        return back()->with('success', 'Portada registrada exitosamente.');
    }

    /**
     * Elimina una portada.
     */
    public function destroyPortada(PortalPortada $portada): RedirectResponse
    {
        if ($portada->imagen && Storage::disk('public')->exists($portada->imagen)) {
            Storage::disk('public')->delete($portada->imagen);
        }

        $portada->delete();

        return back()->with('success', 'Portada eliminada.');
    }

    /**
     * Guarda una publicación o comunicado.
     */
    public function storePublicacion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:Noticia,Comunicado,Convocatoria',
            'descripcion' => 'required|string',
            'contenido' => 'nullable|string',
            'autor' => 'nullable|string|max:100',
            'fecha' => 'nullable|date',
            'imagen' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('portal/publicaciones', 'public');
        }

        $validated['fecha'] = $validated['fecha'] ?? Carbon::now();

        PortalPublicacion::create($validated);

        return back()->with('success', "Se publicó el {$validated['tipo']} exitosamente.");
    }

    /**
     * Elimina una publicación.
     */
    public function destroyPublicacion(PortalPublicacion $publicacion): RedirectResponse
    {
        if ($publicacion->imagen && Storage::disk('public')->exists($publicacion->imagen)) {
            Storage::disk('public')->delete($publicacion->imagen);
        }

        $publicacion->delete();

        return back()->with('success', 'Publicación eliminada.');
    }

    /**
     * Guarda o actualiza una autoridad institucional.
     */
    public function storeAutoridad(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'grado_academico' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:50',
            'orden' => 'nullable|integer',
            'foto' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('portal/autoridades', 'public');
        }

        $validated['orden'] = $validated['orden'] ?? ((PortalAutoridad::max('orden') ?? 0) + 1);

        PortalAutoridad::create($validated);

        return back()->with('success', 'Autoridad registrada.');
    }

    /**
     * Elimina una autoridad.
     */
    public function destroyAutoridad(PortalAutoridad $autoridad): RedirectResponse
    {
        if ($autoridad->foto && Storage::disk('public')->exists($autoridad->foto)) {
            Storage::disk('public')->delete($autoridad->foto);
        }

        $autoridad->delete();

        return back()->with('success', 'Autoridad eliminada.');
    }
}
