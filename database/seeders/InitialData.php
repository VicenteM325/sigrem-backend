<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Estados de ruta
        DB::table('estados_ruta')->insert([
            ['nombre' => 'Activa', 'descripcion' => 'Ruta en operación normal'],
            ['nombre' => 'En mantenimiento', 'descripcion' => 'Ruta en revisión o modificación'],
            ['nombre' => 'Inactiva', 'descripcion' => 'Ruta fuera de servicio']
        ]);

        // Tipos de residuo
        DB::table('tipos_residuo')->insert([
            ['nombre' => 'Orgánico', 'descripcion' => 'Residuos biodegradables'],
            ['nombre' => 'Inorgánico', 'descripcion' => 'Residuos no biodegradables'],
            ['nombre' => 'Mixto', 'descripcion' => 'Mezcla de orgánico e inorgánico']
        ]);

        // Tipos de material reciclable
        DB::table('tipos_material')->insert([
            ['nombre_material' => 'Papel y Cartón', 'descripcion' => 'Periódicos, revistas, cajas'],
            ['nombre_material' => 'Plástico PET', 'descripcion' => 'Botellas de plástico'],
            ['nombre_material' => 'Vidrio', 'descripcion' => 'Envases de vidrio'],
            ['nombre_material' => 'Metal', 'descripcion' => 'Latas de aluminio, hierro'],
            ['nombre_material' => 'Electrónicos', 'descripcion' => 'Aparatos electrónicos']
        ]);
    }
}
