<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Proveedor general',
                'tipo' => 'general',
            ],
            [
                'nombre' => 'Distribuidora principal',
                'tipo' => 'distribuidora',
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::updateOrCreate(
                ['nombre' => $proveedor['nombre']],
                [
                    'tipo' => $proveedor['tipo'],
                    'contacto' => null,
                    'telefono' => null,
                    'direccion' => null,
                    'observacion' => null,
                    'estado' => 'activo',
                ]
            );
        }
    }
}
