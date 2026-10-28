<?php

namespace App\Models;

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
}
