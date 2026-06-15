<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoBarra extends Model
{
    protected $table = 'codigos_barras';

    protected $fillable = [
        'producto_id',
        'producto_presentacion_id',
        'codigo',
        'tipo_codigo',
        'generado_por_sistema',
        'estado',
    ];

    protected $casts = [
        'generado_por_sistema' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }
}
