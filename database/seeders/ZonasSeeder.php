<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonasSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            ['nombre_zona' => 'Zona 1 Centro Histórico', 'densidad_poblacional' => 5],
            ['nombre_zona' => 'Zona 3 La Democracia', 'densidad_poblacional' => 4],
            ['nombre_zona' => 'Zona 3 El Calvario', 'densidad_poblacional' => 4],
            ['nombre_zona' => 'Zona 7 Tecun Human', 'densidad_poblacional' => 3],
            ['nombre_zona' => 'Zona 6 Los Juzgados', 'densidad_poblacional' => 3],
            ['nombre_zona' => 'Zona 8 Colonia el Maestro', 'densidad_poblacional' => 2],
            ['nombre_zona' => 'Zona 9 Templo de Quetzaltenango', 'densidad_poblacional' => 2],
        ];

        foreach ($zonas as $zona) {
            DB::table('zonas')->updateOrInsert(
                ['nombre_zona' => $zona['nombre_zona']],
                [
                    'densidad_poblacional' => $zona['densidad_poblacional'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
