<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioSucursal extends Model
{
     protected $table = 'usuario_sucursal';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'principal',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
