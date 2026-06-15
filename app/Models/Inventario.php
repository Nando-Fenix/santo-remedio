<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'lote_id',
        'stock_actual',
        'stock_minimo',
        'estado',
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id', 'producto_id')
            ->where('sucursal_id', $this->sucursal_id)
            ->where('lote_id', $this->lote_id);
    }

    public function estaBajoStock(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
}
