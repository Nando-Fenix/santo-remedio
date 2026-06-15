<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'producto_presentacion_id',
        'lote_id',
        'cantidad',
        'unidades_ingresadas',
        'precio_compra',
        'subtotal',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
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
