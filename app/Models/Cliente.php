<?php

namespace App\Models;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'ci_nit',
        'telefono',
        'direccion',
        'tipo_cliente',
        'descuento_default',
        'estado',
    ];

    protected $casts = [
        'descuento_default' => 'decimal:2',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
