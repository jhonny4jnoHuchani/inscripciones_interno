<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    use HasFactory;

    /**
     * Nombre explícito de la tabla en español.
     */
    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'nota_final',
        'estado',
        'tipo_registro',
    ];

    protected function casts(): array
    {
        return [
            'nota_final' => 'decimal:2',
        ];
    }

    /**
     * Estudiante vinculado a la inscripción.
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    /**
     * Curso en el que se encuentra inscrito el estudiante.
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Asienta la calificación final y actualiza el estado (Aprobado / Reprobado)
     * según la nota mínima de aprobación configurada en la materia.
     */
    public function asentarNota(float|int|string $nota): self
    {
        // Aseguramos que la relación curso y materia estén cargadas
        $materia = $this->curso?->materia;
        $notaMinima = $materia?->nota_minima_aprobacion ?? 51;

        $this->nota_final = $nota;
        $this->estado = ((float) $nota >= (float) $notaMinima) ? 'Aprobado' : 'Reprobado';
        $this->save();

        return $this;
    }
}
