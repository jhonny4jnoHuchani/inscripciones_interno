<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'director_id',
        'total_niveles',
    ];

    protected function casts(): array
    {
        return [
            'total_niveles' => 'integer',
        ];
    }

    /**
     * Director a cargo de la carrera.
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Estudiantes matriculados en esta carrera.
     */
    public function estudiantes(): HasMany
    {
        return $this->hasMany(User::class, 'carrera_id');
    }

    /**
     * Materias que conforman la malla curricular de la carrera.
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'carrera_id')
            ->orderBy('nivel')
            ->orderBy('sigla');
    }
}
