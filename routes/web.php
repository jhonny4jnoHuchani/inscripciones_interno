<?php

use App\Http\Controllers\MallaCurricularController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
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

    // Admisión de Estudiantes y Kardex Manual
    Route::get('/admisiones', [App\Http\Controllers\AdmisionController::class, 'index'])->name('admisiones.index');
    Route::post('/admisiones/postulantes', [App\Http\Controllers\AdmisionController::class, 'storePostulante'])->name('admisiones.postulantes.store');
    Route::post('/admisiones/{user}/nota', [App\Http\Controllers\AdmisionController::class, 'registrarNota'])->name('admisiones.nota.store');
    Route::post('/admisiones/{user}/inscribir-manual', [App\Http\Controllers\AdmisionController::class, 'inscribirManual'])->name('admisiones.inscribir-manual');
    Route::delete('/admisiones/{user}/inscripciones/{inscripcion}', [App\Http\Controllers\AdmisionController::class, 'desinscribirMateria'])->name('admisiones.desinscribir');

    // Gestión de Roles y Permisos con Descripción
    Route::post('/roles', [App\Http\Controllers\RolePermissionController::class, 'storeRole'])->name('roles.store');
    Route::post('/permisos', [App\Http\Controllers\RolePermissionController::class, 'storePermission'])->name('permisos.store');
    Route::put('/roles/{role}/permisos', [App\Http\Controllers\RolePermissionController::class, 'syncPermissions'])->name('roles.sync-permissions');

    Route::get('/kardex', function () {
        return redirect()->route('admisiones.index');
    })->name('kardex');

    Route::get('/mis-cursos', function () {
        return Inertia::render('MisCursos');
    })->name('mis-cursos');

    Route::get('/mi-panel', function () {
        return Inertia::render('MiPanel');
    })->name('mi-panel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
