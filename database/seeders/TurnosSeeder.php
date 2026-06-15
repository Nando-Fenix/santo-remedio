<?php

namespace Database\Seeders;

use App\Models\Turno;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TurnosSeeder extends Seeder
{
    public function run(): void
    {
        $turnos = [
            [
                'nombre' => 'Mañana',
                'hora_inicio' => '07:00:00',
                'hora_fin' => '14:00:00',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Tarde',
                'hora_inicio' => '14:00:00',
                'hora_fin' => '22:00:00',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Completo',
                'hora_inicio' => '07:00:00',
                'hora_fin' => '22:00:00',
                'estado' => 'activo',
            ],
        ];

        foreach ($turnos as $turno) {
            Turno::updateOrCreate(
                ['nombre' => $turno['nombre']],
                $turno
            );
        }
    }
}
