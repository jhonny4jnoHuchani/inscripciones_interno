<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'periodos_evaluacion';

    protected $fillable = [
        'gestion',
        'numero_parcial',
        'nombre',
        'ponderacion',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'numero_parcial' => 'integer',
        'ponderacion' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
    ];

    protected $appends = ['esta_habilitado', 'estado_texto'];

    public function calificaciones(): HasMany
    {
        return $this->hasMany(CalificacionParcial::class, 'periodo_evaluacion_id');
    }

    public function getEstaHabilitadoAttribute(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->fecha_inicio, $this->fecha_fin);
    }

    public function getEstadoTextoAttribute(): string
    {
        if (!$this->activo) {
            return 'Inactivo';
        }

        $now = Carbon::now();
        if ($now->lt($this->fecha_inicio)) {
            return 'Próximo (Abre ' . $this->fecha_inicio->format('d/m/Y H:i') . ')';
        }
        if ($now->gt($this->fecha_fin)) {
            return 'Cerrado (Finalizó ' . $this->fecha_fin->format('d/m/Y H:i') . ')';
        }

        return 'Habilitado (Cierra ' . $this->fecha_fin->format('d/m/Y H:i') . ')';
    }
}
