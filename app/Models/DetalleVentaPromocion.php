<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaPromocion extends Model
{
    protected $fillable = [
        'venta_id',
        'promocion_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }

    public function items()
    {
        return $this->hasMany(DetalleVentaPromocionItem::class);
    }
}