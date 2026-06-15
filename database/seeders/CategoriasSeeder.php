<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Analgésicos',
            'Antibióticos',
            'Antigripales',
            'Vitaminas',
            'Jarabes',
            'Cremas',
            'Inyectables',
            'Higiene personal',
            'Material médico',
            'Otros',
        ];

        foreach ($categorias as $nombre) {
            Categoria::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => null,
                    'estado' => 'activo',
                ]
            );
        }
    }
}
