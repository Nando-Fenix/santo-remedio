<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'turno_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'total_efectivo',
        'total_qr',
        'total_egresos',
        'total_reembolsos',
        'total_final',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'total_efectivo' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'total_egresos' => 'decimal:2',
        'total_reembolsos' => 'decimal:2',
        'total_final' => 'decimal:2',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'caja_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id');
    }

    public function cierre()
    {
        return $this->hasOne(CierreCaja::class, 'caja_id');
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
