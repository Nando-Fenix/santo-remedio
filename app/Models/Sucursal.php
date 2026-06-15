<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'estado',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_sucursal', 'sucursal_id', 'usuario_id')
            ->withPivot('principal', 'estado')
            ->withTimestamps();
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'sucursal_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'sucursal_id');
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }
    public function cajas()
    {
        return $this->hasMany(Caja::class, 'sucursal_id');
    }

    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class, 'sucursal_id');
    }

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class, 'sucursal_id');
    }
    
    public function compras()
    {
        return $this->hasMany(Compra::class);
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
