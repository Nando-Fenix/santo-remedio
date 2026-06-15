<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArqueoDenominacion extends Model
{
    protected $table = 'arqueo_denominaciones';

    protected $fillable = [
        'cierre_caja_id',
        'denominacion',
        'cantidad',
        'total',
    ];

    protected $casts = [
        'denominacion' => 'decimal:2',
        'cantidad' => 'integer',
        'total' => 'decimal:2',
    ];

    public function cierreCaja()
    {
        return $this->belongsTo(CierreCaja::class, 'cierre_caja_id');
    }
}
