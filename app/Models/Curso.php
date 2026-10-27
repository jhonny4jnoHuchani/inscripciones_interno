<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'materia_id',
        'docente_id',
        'gestion',
        'paralelo',
        'cupo_maximo',
        'fecha_inicio_calificacion',
        'fecha_fin_calificacion',
    ];

    protected function casts(): array
    {
        return [
            'cupo_maximo' => 'integer',
            'fecha_inicio_calificacion' => 'datetime',
            'fecha_fin_calificacion' => 'datetime',
        ];
    }

    /**
     * Materia asociada a este curso.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Docente asignado al curso.
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    /**
     * Inscripciones realizadas en este curso.
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    /**
     * Estudiantes inscritos en este curso.
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'inscripciones',
            'curso_id',
            'estudiante_id'
        )
            ->withPivot(['nota_final', 'estado', 'tipo_registro'])
            ->withTimestamps();
    }

    /**
     * Cantidad de estudiantes inscritos actualmente.
     */
    public function cuposOcupados(): int
    {
        return $this->inscripciones()->count();
    }

    /**
     * Cantidad de cupos restantes disponibles.
     */
    public function cuposDisponibles(): int
    {
        return max(0, $this->cupo_maximo - $this->cuposOcupados());
    }

    /**
     * Evalúa si todavía hay cupo para inscribir a un estudiante.
     */
    public function tieneCupo(): bool
    {
        return $this->cuposDisponibles() > 0;
    }

    /**
     * Evalúa si la fecha actual se encuentra dentro del rango habilitado para calificación.
     */
    public function estaEnVentanaDeCalificacion(): bool
    {
        if (! $this->fecha_inicio_calificacion || ! $this->fecha_fin_calificacion) {
            return false;
        }

        return now()->between($this->fecha_inicio_calificacion, $this->fecha_fin_calificacion);
    }
}
