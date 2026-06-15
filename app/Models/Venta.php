<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'numero_venta',
        'usuario_id',
        'sucursal_id',
        'cliente_id',
        'caja_id',
        'fecha_hora',
        'subtotal',
        'descuento_total',
        'total',
        'monto_recibido',
        'cambio',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_recibido' => 'decimal:2',
        'cambio' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoVenta::class, 'venta_id');
    }

    public function recibo()
    {
        return $this->hasOne(Recibo::class, 'venta_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }
    
    public function reembolsos()
    {
        return $this->hasMany(Reembolso::class);
    }

    public function cambiosProducto()
    {
        return $this->hasMany(CambioProducto::class);
    }
}
