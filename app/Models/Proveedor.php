<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'tipo',
        'contacto',
        'telefono',
        'direccion',
        'observacion',
        'estado',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
    public function compras()
{
    return $this->hasMany(Compra::class);
}
}
