<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalInstitucion extends Model
{
    use HasFactory;

    protected $table = 'portal_institucion';

    protected $fillable = [
        'nombre',
        'sigla',
        'facultad',
        'historia',
        'mision',
        'vision',
        'direccion',
        'telefono',
        'correo',
        'whatsapp',
        'facebook',
    ];
}
