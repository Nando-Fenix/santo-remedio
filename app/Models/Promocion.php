<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'tipo',
        'precio_promocional',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'precio_promocional' => 'decimal:2',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function items()
    {
        return $this->hasMany(PromocionItem::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function promocionItems()
    {
        return $this->hasMany(PromocionItem::class, 'producto_id');
    }

    public function ventas()
    {
        return $this->hasMany(DetalleVentaPromocion::class);
    }
}
