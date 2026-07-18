<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'numero_lote',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'lote_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesReembolso()
    {
        return $this->hasMany(DetalleReembolso::class);
    }

    public function detallesCambiosDevueltos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'lote_devuelto_id');
    }

    public function detallesCambiosNuevos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'lote_nuevo_id');
    }

    public function promocionItems()
    {
        return $this->hasMany(PromocionItem::class, 'lote_id');
    }

    public function bajasInventario()
    {
        return $this->hasMany(BajaInventario::class);
    }
}
