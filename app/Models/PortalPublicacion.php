<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalPublicacion extends Model
{
    use HasFactory;

    protected $table = 'portal_publicaciones';

    protected $fillable = [
        'titulo',
        'descripcion',
        'contenido',
        'imagen',
        'tipo',
        'fecha',
        'autor',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    protected $appends = ['imagen_url'];

    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->imagen
                ? (str_starts_with($this->imagen, 'http') ? $this->imagen : asset('storage/' . ltrim($this->imagen, '/')))
                : null
        );
    }
}
