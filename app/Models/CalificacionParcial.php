<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionParcial extends Model
{
    use HasFactory;

    protected $table = 'calificaciones_parciales';

    protected $fillable = [
        'inscripcion_id',
        'periodo_evaluacion_id',
        'nota',
        'observacion',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
    ];

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'periodo_evaluacion_id');
    }
}
