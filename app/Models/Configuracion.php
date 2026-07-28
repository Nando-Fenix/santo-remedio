<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre_farmacia',
        'nit',
        'telefono',
        'direccion',
        'ciudad',
        'moneda',
        'mensaje_recibo',
        'logo',
    ];
}