<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioFarmaciaInsumo extends Model
{
    protected $table = 'servicio_farmacia_insumos';

    protected $fillable = [
        'servicio_farmacia_id',
        'producto_id',
        'producto_presentacion_id',
        'cantidad',
        'unidades_necesarias',
    ];

    public function servicio()
    {
        return $this->belongsTo(ServicioFarmacia::class, 'servicio_farmacia_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class);
    }
}