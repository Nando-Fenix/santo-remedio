<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Tiene acceso completo al sistema.',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Vendedor',
                'descripcion' => 'Puede realizar ventas, manejar caja, inventario y consultar información permitida.',
                'estado' => 'activo',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }
    }
}
