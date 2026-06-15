<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetodosPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            [
                'nombre' => 'Efectivo',
                'tipo' => 'efectivo',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Transferencia Qr',
                'tipo' => 'qr',
                'estado' => 'activo',
            ],
            
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(
                ['nombre' => $metodo['nombre']],
                $metodo
            );
        }
    }
}
