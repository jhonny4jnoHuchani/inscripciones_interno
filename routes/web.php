<?php

use App\Http\Controllers\MallaCurricularController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\WebPublicaController::class, 'index'])->name('home');
Route::post('/consulta-postulacion', [App\Http\Controllers\WebPublicaController::class, 'consultarPostulacion'])->name('consulta.postulacion');

Route::get('/dashboard', function () {
    $totalPostulantes = \App\Models\User::whereNotNull('estado_admision')->count();
    $admitidos = \App\Models\User::where('estado_admision', 'Admitido')->count();
    $cursosActivos = \App\Models\Curso::where('gestion', '2026-I')->count();
    $materiasTotal = \App\Models\Materia::count();
    $docentesTotal = \App\Models\User::role('Docente')->count();
    $inscripcionesActivas = \App\Models\Inscripcion::whereHas('curso', fn ($q) => $q->where('gestion', '2026-I'))->count();

    return Inertia::render('Dashboard', [
        'stats' => [
            'totalPostulantes' => $totalPostulantes,
            'admitidos' => $admitidos,
            'tasaAprobacion' => $totalPostulantes > 0 ? round(($admitidos / $totalPostulantes) * 100, 1) : 0,
            'cursosActivos' => $cursosActivos,
            'materiasTotal' => $materiasTotal,
            'docentesTotal' => $docentesTotal,
            'inscripcionesActivas' => $inscripcionesActivas,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Malla Curricular Interactiva
    Route::get('/malla-curricular', [MallaCurricularController::class, 'index'])->name('malla-curricular.index');
    Route::post('/malla-curricular/materias', [MallaCurricularController::class, 'storeMateria'])->name('malla-curricular.materias.store');
    Route::put('/malla-curricular/materias/{materia}', [MallaCurricularController::class, 'updateMateria'])->name('malla-curricular.materias.update');
    Route::delete('/malla-curricular/materias/{materia}', [MallaCurricularController::class, 'destroyMateria'])->name('malla-curricular.materias.destroy');
    Route::post('/malla-curricular/toggle-requisito', [MallaCurricularController::class, 'toggleRequisito'])->name('malla-curricular.toggle-requisito');

    // Cursos, Paralelos y Asignación Docente
    Route::get('/cursos', [App\Http\Controllers\CursoController::class, 'index'])->name('cursos.index');
    Route::post('/cursos', [App\Http\Controllers\CursoController::class, 'store'])->name('cursos.store');
    Route::put('/cursos/{curso}', [App\Http\Controllers\CursoController::class, 'update'])->name('cursos.update');
    Route::delete('/cursos/{curso}', [App\Http\Controllers\CursoController::class, 'destroy'])->name('cursos.destroy');
    Route::post('/cursos/{curso}/asignar-docente', [App\Http\Controllers\CursoController::class, 'asignarDocente'])->name('cursos.asignar-docente');

    // Módulo de Docentes y Carga Horaria
    Route::get('/docentes', [App\Http\Controllers\DocenteController::class, 'index'])->name('docentes.index');
    Route::post('/docentes', [App\Http\Controllers\DocenteController::class, 'store'])->name('docentes.store');
    Route::put('/docentes/{docente}', [App\Http\Controllers\DocenteController::class, 'update'])->name('docentes.update');
    Route::delete('/docentes/{docente}', [App\Http\Controllers\DocenteController::class, 'destroy'])->name('docentes.destroy');

    // Admisiones de Postulantes (Examen de ingreso y habilitación primer semestre)
    Route::get('/admisiones', [App\Http\Controllers\AdmisionController::class, 'index'])->name('admisiones.index');
    Route::post('/admisiones/postulantes', [App\Http\Controllers\AdmisionController::class, 'storePostulante'])->name('admisiones.postulantes.store');
    Route::post('/admisiones/{user}/nota', [App\Http\Controllers\AdmisionController::class, 'registrarNota'])->name('admisiones.nota.store');
    Route::post('/admisiones/{user}/inscribir-manual', [App\Http\Controllers\AdmisionController::class, 'inscribirManual'])->name('admisiones.inscribir-manual');
    Route::delete('/admisiones/{user}/inscripciones/{inscripcion}', [App\Http\Controllers\AdmisionController::class, 'desinscribirMateria'])->name('admisiones.desinscribir');

    // Kardex Académico Independiente (Estudiantes regulares, historial e inscripción manual)
    Route::get('/kardex', [App\Http\Controllers\KardexController::class, 'index'])->name('kardex.index');
    Route::post('/kardex/{user}/inscribir', [App\Http\Controllers\KardexController::class, 'inscribirManual'])->name('kardex.inscribir');
    Route::delete('/kardex/{user}/inscripciones/{inscripcion}', [App\Http\Controllers\KardexController::class, 'desinscribirMateria'])->name('kardex.desinscribir');

    // Configuración Académica (Semestres por carrera y Cronograma de Parciales con Fechas)
    Route::get('/configuracion-academica', [App\Http\Controllers\ConfiguracionAcademicaController::class, 'index'])->name('configuracion-academica.index');
    Route::put('/configuracion-academica/carreras/{carrera}/niveles', [App\Http\Controllers\ConfiguracionAcademicaController::class, 'updateCarreraNiveles'])->name('configuracion-academica.carreras.update-niveles');
    Route::post('/configuracion-academica/periodos', [App\Http\Controllers\ConfiguracionAcademicaController::class, 'storePeriodo'])->name('configuracion-academica.periodos.store');
    Route::put('/configuracion-academica/periodos/{periodo}', [App\Http\Controllers\ConfiguracionAcademicaController::class, 'updatePeriodo'])->name('configuracion-academica.periodos.update');
    Route::delete('/configuracion-academica/periodos/{periodo}', [App\Http\Controllers\ConfiguracionAcademicaController::class, 'destroyPeriodo'])->name('configuracion-academica.periodos.destroy');

    // Panel del Docente (Mis Cursos y Calificación por Parciales con Validación de Fechas)
    Route::get('/mis-cursos', [App\Http\Controllers\MisCursosController::class, 'index'])->name('mis-cursos.index');
    Route::post('/mis-cursos/{curso}/guardar-notas', [App\Http\Controllers\MisCursosController::class, 'guardarNotas'])->name('mis-cursos.guardar-notas');

    // Panel del Estudiante (Portal Alumno con Materias y Notas Parciales)
    Route::get('/mi-panel', [App\Http\Controllers\MiPanelController::class, 'index'])->name('mi-panel.index');

    // Gestión de Roles y Permisos con Descripción
    Route::post('/roles', [App\Http\Controllers\RolePermissionController::class, 'storeRole'])->name('roles.store');
    Route::post('/permisos', [App\Http\Controllers\RolePermissionController::class, 'storePermission'])->name('permisos.store');
    Route::put('/roles/{role}/permisos', [App\Http\Controllers\RolePermissionController::class, 'syncPermissions'])->name('roles.sync-permissions');

    // Gestión del Portal Web Institucional
    Route::get('/portal-web', [App\Http\Controllers\PortalWebAdminController::class, 'index'])->name('portal-web.index');
    Route::put('/portal-web/institucion', [App\Http\Controllers\PortalWebAdminController::class, 'updateInstitucion'])->name('portal-web.institucion.update');
    Route::post('/portal-web/publicaciones', [App\Http\Controllers\PortalWebAdminController::class, 'storePublicacion'])->name('portal-web.publicaciones.store');
    Route::delete('/portal-web/publicaciones/{publicacion}', [App\Http\Controllers\PortalWebAdminController::class, 'destroyPublicacion'])->name('portal-web.publicaciones.destroy');
    Route::post('/portal-web/autoridades', [App\Http\Controllers\PortalWebAdminController::class, 'storeAutoridad'])->name('portal-web.autoridades.store');
    Route::delete('/portal-web/autoridades/{autoridad}', [App\Http\Controllers\PortalWebAdminController::class, 'destroyAutoridad'])->name('portal-web.autoridades.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
