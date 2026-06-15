<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    protected $table = 'cierres_caja';

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'sucursal_id',
        'turno_id',
        'fecha_cierre',
        'efectivo_sistema',
        'efectivo_contado',
        'diferencia_efectivo',
        'qr_sistema',
        'qr_verificado',
        'diferencia_qr',
        'total_egresos',
        'total_reembolsos',
        'monto_retiro_ahorro',
        'efectivo_final_despues_ahorro',
        'total_sistema',
        'total_verificado',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'efectivo_sistema' => 'decimal:2',
        'efectivo_contado' => 'decimal:2',
        'diferencia_efectivo' => 'decimal:2',
        'qr_sistema' => 'decimal:2',
        'qr_verificado' => 'decimal:2',
        'diferencia_qr' => 'decimal:2',
        'total_egresos' => 'decimal:2',
        'total_reembolsos' => 'decimal:2',
        'monto_retiro_ahorro' => 'decimal:2',
        'efectivo_final_despues_ahorro' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'total_verificado' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function denominaciones()
    {
        return $this->hasMany(ArqueoDenominacion::class, 'cierre_caja_id');
    }
}
