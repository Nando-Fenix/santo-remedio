<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();

        $admin = User::updateOrCreate(
            ['usuario' => 'admin'],
            [
                'nombre' => 'Administrador Santo Remedio',
                'ci' => '0000000',
                'usuario' => 'admin',
                'password' => Hash::make('admin12345'),
                'rol_id' => $rolAdmin?->id,
                'estado' => 'activo',
            ]
        );

        $sucursales = Sucursal::where('estado', 'activo')->get();

        foreach ($sucursales as $index => $sucursal) {
            $admin->sucursales()->syncWithoutDetaching([
                $sucursal->id => [
                    'principal' => $index === 0,
                    'estado' => 'activo',
                ],
            ]);
        }
    }
}
