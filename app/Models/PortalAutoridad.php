<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalAutoridad extends Model
{
    use HasFactory;

    protected $table = 'portal_autoridades';

    protected $fillable = [
        'nombre',
        'cargo',
        'grado_academico',
        'foto',
        'telefono',
        'correo',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    protected $appends = ['foto_url'];

    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->foto
                ? (str_starts_with($this->foto, 'http') ? $this->foto : asset('storage/' . ltrim($this->foto, '/')))
                : null
        );
    }
}
