<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPresentacion extends Model
{
    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'producto_id',
        'presentacion_id',
        'nombre_mostrado',
        'unidades_equivalentes',
        'precio_compra',
        'precio_venta',
        'es_principal',
        'estado',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'presentacion_id');
    }

    public function codigosBarras()
    {
        return $this->hasMany(CodigoBarra::class, 'producto_presentacion_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_presentacion_id');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function detallesReembolso()
    {
        return $this->hasMany(DetalleReembolso::class);
    }

    public function detallesCambiosDevueltos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'producto_presentacion_devuelta_id');
    }

    public function detallesCambiosNuevos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'producto_presentacion_nueva_id');
    }

    public function promocionItems()
    {
        return $this->hasMany(PromocionItem::class);
    }

    public function servicioFarmaciaInsumos()
    {
        return $this->hasMany(ServicioFarmaciaInsumo::class);
    }

    public function atencionServicioInsumos()
    {
        return $this->hasMany(AtencionServicioInsumo::class);
    }
}
