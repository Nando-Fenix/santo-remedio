<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionItem extends Model
{
    protected $fillable = [
        'promocion_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'cantidad',
        'unidades_necesarias',
        'precio_referencia',
    ];

    protected $casts = [
        'precio_referencia' => 'decimal:2',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
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

    public function promocionItems()
    {
        return $this->hasMany(PromocionItem::class, 'producto_presentacion_id');
    }

    public function detallesVentaPromocion()
    {
        return $this->hasMany(DetalleVentaPromocionItem::class);
    }
}