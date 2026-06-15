<?php

namespace Database\Seeders;

use App\Models\Presentacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PresentacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presentaciones = [
            ['nombre' => 'Unidad', 'descripcion' => 'Venta individual o unidad mínima.'],
            ['nombre' => 'Caja', 'descripcion' => 'Presentación por caja.'],
            ['nombre' => 'Blíster', 'descripcion' => 'Presentación por blíster.'],
            ['nombre' => 'Frasco', 'descripcion' => 'Presentación por frasco.'],
            ['nombre' => 'Tubo', 'descripcion' => 'Presentación por tubo.'],
            ['nombre' => 'Ampolla', 'descripcion' => 'Presentación por ampolla.'],
            ['nombre' => 'Sobre', 'descripcion' => 'Presentación por sobre.'],
            ['nombre' => 'Botella', 'descripcion' => 'Presentación por botella.'],
            ['nombre' => 'Ml', 'descripcion' => 'Presentación medida en mililitros.'],
        ];

        foreach ($presentaciones as $presentacion) {
            Presentacion::updateOrCreate(
                ['nombre' => $presentacion['nombre']],
                [
                    'descripcion' => $presentacion['descripcion'],
                    'estado' => 'activo',
                ]
            );
        }
    }
}
