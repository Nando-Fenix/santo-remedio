<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
    ];

    public function pagosVenta()
    {
        return $this->hasMany(PagoVenta::class, 'metodo_pago_id');
    }
}
