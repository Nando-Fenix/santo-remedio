<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionServicio extends Model
{
    protected $table = 'atenciones_servicio';

    protected $fillable = [
        'servicio_farmacia_id',
        'sucursal_id',
        'usuario_id',
        'cliente_id',
        'caja_id',
        'metodo_pago_id',
        'fecha_hora',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'observacion',
        'motivo_anulacion',
        'fecha_anulacion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function servicio()
    {
        return $this->belongsTo(ServicioFarmacia::class, 'servicio_farmacia_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function insumos()
    {
        return $this->hasMany(AtencionServicioInsumo::class);
    }
}