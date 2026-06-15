<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoCompra extends Model
{
    protected $table = 'pagos_compras';

    protected $fillable = [
        'compra_id',
        'usuario_id',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'referencia',
        'observacion',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
