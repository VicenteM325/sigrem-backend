<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CamionesSeeder extends Seeder
{
    public function run(): void
    {

        $camiones = [

            [
                'placa' => 'C-001ABC',
                'capacidad_toneladas' => 10.50,
                'estado_vehiculo' => 'operativo',
                'id_conductor' => null
            ],

            [
                'placa' => 'C-002ABC',
                'capacidad_toneladas' => 14.80,
                'estado_vehiculo' => 'operativo',
                'id_conductor' => null
            ],

            [
                'placa' => 'C-003ABC',
                'capacidad_toneladas' => 13.20,
                'estado_vehiculo' => 'mantenimiento',
                'id_conductor' => null
            ],

            [
                'placa' => 'C-004ABC',
                'capacidad_toneladas' => 12.90,
                'estado_vehiculo' => 'operativo',
                'id_conductor' => null
            ],

            [
                'placa' => 'C-005ABC',
                'capacidad_toneladas' => 15.00,
                'estado_vehiculo' => 'fuera_servicio',
                'id_conductor' => null
            ],

        ];

        foreach ($camiones as $camion) {

            DB::table('camiones')->updateOrInsert(
                ['placa' => $camion['placa']],
                $camion + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        }

        // Sincronizar autoincrement para PostgreSQL
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('camiones', 'id_camion'),
                (SELECT MAX(id_camion) FROM camiones)
            )
        ");
    }
}
