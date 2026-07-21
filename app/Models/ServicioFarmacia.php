<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioFarmacia extends Model
{
    protected $table = 'servicios_farmacia';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'tipo',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function insumos()
    {
        return $this->hasMany(ServicioFarmaciaInsumo::class);
    }

    public function atenciones()
    {
        return $this->hasMany(AtencionServicio::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}