<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'turno_id');
    }

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class, 'turno_id');
    }
}
