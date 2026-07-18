<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaPromocionItem extends Model
{
    protected $table = 'detalle_venta_promocion_items';
    
    protected $fillable = [
        'detalle_venta_promocion_id',
        'promocion_item_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'unidades_descontadas',
    ];

    public function detalleVentaPromocion()
    {
        return $this->belongsTo(DetalleVentaPromocion::class);
    }

    public function promocionItem()
    {
        return $this->belongsTo(PromocionItem::class);
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