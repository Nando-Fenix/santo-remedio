<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CambioProducto extends Model
{
    protected $table = 'cambios_producto';

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'sucursal_id',
        'caja_id',
        'numero_cambio',
        'fecha_cambio',
        'monto_devuelto',
        'monto_nuevo',
        'diferencia',
        'tipo_diferencia',
        'motivo',
        'estado',
        'usuario_anulacion_id',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'monto_devuelto' => 'decimal:2',
        'monto_nuevo' => 'decimal:2',
        'diferencia' => 'decimal:2',
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
        return $this->hasMany(DetalleCambioProducto::class, 'cambio_producto_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'usuario_anulacion_id');
    }
}
