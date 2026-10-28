<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalPortada extends Model
{
    use HasFactory;

    protected $table = 'portal_portadas';

    protected $fillable = [
        'titulo',
        'subtitulo',
        'badge',
        'imagen',
        'link_url',
        'link_texto',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
}
