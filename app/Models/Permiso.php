<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
     protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'modulo',
        'descripcion',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso')
            ->withTimestamps();
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_permiso')
            ->withTimestamps();
    }
}
