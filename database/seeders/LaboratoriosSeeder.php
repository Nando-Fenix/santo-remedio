<?php

namespace Database\Seeders;

use App\Models\Laboratorio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratoriosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laboratorios = [
            'INTI',
            'COFAR',
            'Bagó',
            'Bayer',
            'Terbol',
            'Genérico',
            'Otro',
        ];

        foreach ($laboratorios as $nombre) {
            Laboratorio::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => null,
                    'estado' => 'activo',
                ]
            );
        }
    }
}
