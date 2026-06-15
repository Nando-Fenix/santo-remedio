<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SucursalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sucursales = [
            [
                'nombre' => 'Sucursal Central',
                'direccion' => null,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Sucursal Secundaria',
                'direccion' => null,
                'estado' => 'activo',
            ],
        ];

        foreach ($sucursales as $sucursal) {
            Sucursal::updateOrCreate(
                ['nombre' => $sucursal['nombre']],
                $sucursal
            );
        }
    }
}
