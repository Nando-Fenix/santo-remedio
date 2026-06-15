<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
     protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'cantidad',
        'unidades_descontadas',
        'precio_unitario',
        'descuento',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'unidades_descontadas' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function lotesDescontados()
    {
        return $this->hasMany(DetalleVentaLote::class, 'detalle_venta_id');
    }

    public function reembolsos()
    {
        return $this->hasMany(DetalleReembolso::class);
    }

    public function cambiosProductoDevueltos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'detalle_venta_devuelto_id');
    }
}
