<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaInventario extends Model
{
    protected $table = 'bajas_inventario';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'lote_id',
        'usuario_id',
        'motivo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'observacion',
        'estado',
        'usuario_anulacion_id',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_anulacion' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'usuario_anulacion_id');
    }
}