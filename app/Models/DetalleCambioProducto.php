<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCambioProducto extends Model
{
    protected $table = 'detalle_cambios_producto';

    protected $fillable = [
        'cambio_producto_id',
        'detalle_venta_devuelto_id',

        'producto_devuelto_id',
        'producto_presentacion_devuelta_id',
        'lote_devuelto_id',
        'cantidad_devuelta',
        'unidades_devueltas',
        'monto_devuelto',

        'producto_nuevo_id',
        'producto_presentacion_nueva_id',
        'lote_nuevo_id',
        'cantidad_nueva',
        'unidades_nuevas',
        'precio_unitario_nuevo',
        'monto_nuevo',
    ];

    protected $casts = [
        'monto_devuelto' => 'decimal:2',
        'precio_unitario_nuevo' => 'decimal:2',
        'monto_nuevo' => 'decimal:2',
    ];

    public function cambioProducto()
    {
        return $this->belongsTo(CambioProducto::class, 'cambio_producto_id');
    }

    public function detalleVentaDevuelto()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_devuelto_id');
    }

    public function productoDevuelto()
    {
        return $this->belongsTo(Producto::class, 'producto_devuelto_id');
    }

    public function productoPresentacionDevuelta()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_devuelta_id');
    }

    public function loteDevuelto()
    {
        return $this->belongsTo(Lote::class, 'lote_devuelto_id');
    }

    public function productoNuevo()
    {
        return $this->belongsTo(Producto::class, 'producto_nuevo_id');
    }

    public function productoPresentacionNueva()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_nueva_id');
    }

    public function loteNuevo()
    {
        return $this->belongsTo(Lote::class, 'lote_nuevo_id');
    }
}
