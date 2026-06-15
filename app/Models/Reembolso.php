<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reembolso extends Model
{
    protected $fillable = [
        'venta_id',
        'usuario_id',
        'sucursal_id',
        'caja_id',
        'numero_reembolso',
        'fecha_reembolso',
        'monto_total',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'fecha_reembolso' => 'datetime',
        'monto_total' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleReembolso::class);
    }
}
