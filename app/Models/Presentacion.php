<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    protected $table = 'presentaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function productoPresentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class, 'presentacion_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_presentaciones', 'presentacion_id', 'producto_id')
            ->withPivot([
                'nombre_mostrado',
                'unidades_equivalentes',
                'precio_compra',
                'precio_venta',
                'es_principal',
                'estado',
            ])
            ->withTimestamps();
    }
}
