<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'carrera_id',
    'ci',
    'nota_admision',
    'modalidad_admision',
    'estado_admision',
    'fecha_admision',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'nota_admision' => 'decimal:2',
            'fecha_admision' => 'date',
        ];
    }

    /**
     * Carrera a la que pertenece el usuario (ej: Estudiante o Postulante).
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    /**
     * Carrera dirigida por el usuario (si tiene rol Director).
     */
    public function carreraDirigida(): HasOne
    {
        return $this->hasOne(Carrera::class, 'director_id');
    }

    /**
     * Inscripciones realizadas por el usuario como estudiante.
     */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'estudiante_id');
    }

    /**
     * Cursos impartidos por el usuario como docente.
     */
    public function cursosDocente(): HasMany
    {
        return $this->hasMany(Curso::class, 'docente_id');
    }

    /**
     * Cursos en los que participa como estudiante.
     */
    public function cursosComoEstudiante(): BelongsToMany
    {
        return $this->belongsToMany(
            Curso::class,
            'inscripciones',
            'estudiante_id',
            'curso_id'
        )
            ->withPivot(['nota_final', 'estado', 'tipo_registro'])
            ->withTimestamps();
    }

    /**
     * Evalúa y procesa la admisión del estudiante según la nota (>= 61) o modalidad.
     */
    public function evaluarAdmision(?float $nota, string $modalidad = 'Examen de Admisión'): bool
    {
        $this->nota_admision = $nota;
        $this->modalidad_admision = $modalidad;

        // Regla: Aceptado con nota >= 61 o por modalidad especial (Excelencia Académica)
        if ($modalidad === 'Excelencia Académica' || ($nota !== null && $nota >= 61)) {
            $this->estado_admision = 'Admitido';
            $this->fecha_admision = now();
            if (! $this->hasRole('Estudiante')) {
                $this->assignRole('Estudiante');
            }
            $admitido = true;
        } else {
            $this->estado_admision = 'No Admitido';
            $this->fecha_admision = now();
            $admitido = false;
        }

        $this->save();
        return $admitido;
    }

    /**
     * Comprueba si el estudiante está formalmente admitido para poder inscribirse a 1er semestre.
     */
    public function estaAdmitido(): bool
    {
        return $this->estado_admision === 'Admitido';
    }

    /**
     * Retorna true si tiene una inscripción en estado 'Aprobado' para esa materia.
     */
    public function haAprobadoMateria(int|string $materia_id): bool
    {
        return $this->inscripciones()
            ->where('estado', 'Aprobado')
            ->whereHas('curso', function ($query) use ($materia_id) {
                $query->where('materia_id', $materia_id);
            })
            ->exists();
    }

    /**
     * Revisa los pre-requisitos de la materia.
     * Si la materia exige requisitos, verifica iterando con haAprobadoMateria().
     * Retorna true si cumple todo o si la materia no tiene requisitos.
     */
    public function cumpleRequisitosPara(Materia $materia): bool
    {
        $requisitos = $materia->requisitos;

        if ($requisitos->isEmpty()) {
            return true;
        }

        foreach ($requisitos as $requisito) {
            if (! $this->haAprobadoMateria($requisito->id)) {
                return false;
            }
        }

        return true;
    }
}
