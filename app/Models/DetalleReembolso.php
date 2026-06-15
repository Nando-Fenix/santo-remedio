<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleReembolso extends Model
{
    protected $table = 'detalle_reembolsos';

    protected $fillable = [
        'reembolso_id',
        'detalle_venta_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'cantidad_devuelta',
        'unidades_devueltas',
        'monto_devuelto',
    ];

    protected $casts = [
        'monto_devuelto' => 'decimal:2',
    ];

    public function reembolso()
    {
        return $this->belongsTo(Reembolso::class);
    }

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class);
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
