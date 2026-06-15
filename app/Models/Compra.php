<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'sucursal_id',
        'usuario_id',
        'numero_compra',
        'fecha_compra',
        'subtotal',
        'descuento_total',
        'total',
        'monto_pagado',
        'saldo_pendiente',
        'tipo_pago',
        'estado_pago',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_compra' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoCompra::class);
    }
}
