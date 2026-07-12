<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre_comercial',
        'nombre_generico',
        'descripcion',
        'concentracion',
        'categoria_id',
        'laboratorio_id',
        'proveedor_id',
        'estado',
        'tipo_producto',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function presentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class, 'producto_id');
    }

    public function codigosBarras()
    {
        return $this->hasMany(CodigoBarra::class, 'producto_id');
    }

    public function presentacionPrincipal()
    {
        return $this->hasOne(ProductoPresentacion::class, 'producto_id')
            ->where('es_principal', true)
            ->where('estado', 'activo');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
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
        return $this->hasMany(DetalleCambioProducto::class, 'producto_devuelto_id');
    }

    public function detallesCambiosNuevos()
    {
        return $this->hasMany(DetalleCambioProducto::class, 'producto_nuevo_id');
    }

}
