<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionServicioInsumo extends Model
{
    protected $table = 'atencion_servicio_insumos';

    protected $fillable = [
        'atencion_servicio_id',
        'servicio_farmacia_insumo_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'unidades_descontadas',
    ];

    public function atencionServicio()
    {
        return $this->belongsTo(AtencionServicio::class);
    }

    public function servicioFarmaciaInsumo()
    {
        return $this->belongsTo(ServicioFarmaciaInsumo::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}