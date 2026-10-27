<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrera_id',
        'nombre',
        'sigla',
        'nota_minima_aprobacion',
        'nivel',
    ];

    protected function casts(): array
    {
        return [
            'nota_minima_aprobacion' => 'integer',
            'nivel' => 'integer',
        ];
    }

    /**
     * Carrera a la que pertenece esta materia.
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    /**
     * Pre-requisitos exigidos para poder cursar esta materia.
     */
    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_requisito',
            'materia_id',
            'requisito_id'
        )->withTimestamps();
    }

    /**
     * Materias posteriores que exigen esta materia como pre-requisito.
     */
    public function materiasQueLaExigen(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_requisito',
            'requisito_id',
            'materia_id'
        )->withTimestamps();
    }

    /**
     * Cursos o paralelos abiertos de esta materia.
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }
}
