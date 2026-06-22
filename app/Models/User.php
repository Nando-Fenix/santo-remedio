<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Permiso;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'nombre',
        'ci',
        'usuario',
        'password',
        'rol_id',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'usuario_sucursal', 'usuario_id', 'sucursal_id')
            ->withPivot('principal', 'estado')
            ->withTimestamps();
    }

    public function sucursalPrincipal()
    {
        return $this->sucursales()
            ->wherePivot('principal', true)
            ->wherePivot('estado', 'activo');
    }
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'usuario_id');
    }

    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class, 'usuario_id');
    }

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class, 'usuario_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    public function pagosCompras()
    {
        return $this->hasMany(PagoCompra::class, 'usuario_id');
    }

    public function reembolsos()
    {
        return $this->hasMany(Reembolso::class, 'usuario_id');
    }

    public function cambiosProducto()
    {
        return $this->hasMany(CambioProducto::class, 'usuario_id');
    }

    public function permisosDirectos()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso')
            ->withTimestamps();
    }

    public function tienePermiso(string $nombrePermiso): bool
    {
        /*
        * El administrador tiene acceso total automáticamente.
        */
        if ($this->rol && $this->rol->nombre === 'Administrador') {
            return true;
        }

        /*
        * Primero revisa permisos directos asignados al usuario.
        */
        if ($this->permisosDirectos()
            ->where('nombre', $nombrePermiso)
            ->exists()) {
            return true;
        }

        /*
        * Luego revisa permisos heredados desde el rol.
        */
        if ($this->rol && $this->rol->permisos()
            ->where('nombre', $nombrePermiso)
            ->exists()) {
            return true;
        }

        return false;
    }
}
