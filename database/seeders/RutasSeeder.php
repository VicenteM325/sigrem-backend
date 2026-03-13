<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RutasSeeder extends Seeder
{
    public function run(): void
    {

        $rutas = [

            [
                'nombre_ruta' => 'Ruta Zona 1',
                'descripcion' => 'Parque central, hasta Universidad San Carlos de Guatemala',
                'id_zona' => 1,
                'id_estado_ruta' => 1,
                'coordenada_inicio_lat' => 14.84630237,
                'coordenada_inicio_lng' => -91.53656682,
                'coordenada_fin_lat' => 14.84648243,
                'coordenada_fin_lng' => -91.53659070,
                'distancia_km' => 8.10,
                'horario_inicio' => '06:00:00',
                'horario_fin' => '12:00:00',
            ],

            [
                'nombre_ruta' => 'Ruta Zona 3',
                'descripcion' => 'Zona Comercial',
                'id_zona' => 2,
                'id_estado_ruta' => 1,
                'coordenada_inicio_lat' => 14.84644292,
                'coordenada_inicio_lng' => -91.53654680,
                'coordenada_fin_lat' => 14.84636007,
                'coordenada_fin_lng' => -91.53647143,
                'distancia_km' => 6.80,
                'horario_inicio' => '06:00:00',
                'horario_fin' => '12:00:00',
            ],

            [
                'nombre_ruta' => 'Ruta Monumento Tecun',
                'descripcion' => 'Ruta a monumento tecun, 19 avenida',
                'id_zona' => 4,
                'id_estado_ruta' => 1,
                'coordenada_inicio_lat' => 14.84803273,
                'coordenada_inicio_lng' => -91.52074371,
                'coordenada_fin_lat' => 14.84795721,
                'coordenada_fin_lng' => -91.52083526,
                'distancia_km' => 6.30,
                'horario_inicio' => '06:00:00',
                'horario_fin' => '12:00:00',
            ]

        ];

        foreach ($rutas as $ruta) {

            DB::table('rutas')->updateOrInsert(
                ['nombre_ruta' => $ruta['nombre_ruta']],
                $ruta + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        }

        // Sincronizar autoincrement
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('rutas', 'id_ruta'),
                (SELECT MAX(id_ruta) FROM rutas)
            )
        ");
    }
}
