<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conductor;
use App\Models\Ciudadano;

use Illuminate\Support\Facades\Hash;


class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [

            // ADMINISTRADORES
            [
                'name'=>'Mauricio Ordoñez',
                'email'=>'mauricio@sigrem.com',
                'telefono'=>'45567896',
                'direccion'=>'Zona 1 Quetzaltenango',
                'nombres'=>'Mauricio',
                'apellidos'=>'Ordoñez',
                'rol'=>'administrador'
            ],
            [
                'name'=>'Juan Arbeloa',
                'email'=>'juan@sigrem.com',
                'telefono'=>'45567589',
                'direccion'=>'Zona 1 Quetzaltenango',
                'nombres'=>'Juan',
                'apellidos'=>'Arbeloa',
                'rol'=>'administrador'
            ],
            [
                'name'=>'Pedro Alvarez',
                'email'=>'pedro@sigrem.com',
                'telefono'=>'46585826',
                'direccion'=>'Zona 3 Quetzaltenango',
                'nombres'=>'Pedro',
                'apellidos'=>'Alvarez',
                'rol'=>'administrador'
            ],

            // SUPERVISOR-RUTAS
            [
                'name'=>'Selvyn Mendez',
                'email'=>'selvyn@sigrem.com',
                'telefono'=>'45567896',
                'direccion'=>'Zona 8 Quetzaltenango',
                'nombres'=>'Selvyn',
                'apellidos'=>'Mendez',
                'rol'=>'supervisor-rutas'
            ],
            [
                'name'=>'Julio Pancracio',
                'email'=>'julio@sigrem.com',
                'telefono'=>'41367589',
                'direccion'=>'Zona 1 Quetzaltenango',
                'nombres'=>'Julio',
                'apellidos'=>'Pancracio',
                'rol'=>'supervisor-rutas'
            ],
            [
                'name'=>'Franco Morillo',
                'email'=>'franco@sigrem.com',
                'telefono'=>'46565826',
                'direccion'=>'Zona 3 Quetzaltenango',
                'nombres'=>'Franco',
                'apellidos'=>'Morillo',
                'rol'=>'supervisor-rutas'
            ],

            // CONDUCTORES
            [
                'name'=>'Carlos Lopez',
                'email'=>'carlos@sigrem.com',
                'rol'=>'conductor'
            ],
            [
                'name'=>'Luis Perez',
                'email'=>'luis@sigrem.com',
                'rol'=>'conductor'
            ],
            [
                'name'=>'Mario Gomez',
                'email'=>'mario@sigrem.com',
                'rol'=>'conductor'
            ],

            // ENCARGADO PUNTO VERDE
            [
                'name'=>'Ana Morales',
                'email'=>'ana@sigrem.com',
                'rol'=>'encargado-punto-verde'
            ],
            [
                'name'=>'Karla Díaz',
                'email'=>'karla@sigrem.com',
                'rol'=>'encargado-punto-verde'
            ],
            [
                'name'=>'Roberto Soto',
                'email'=>'roberto@sigrem.com',
                'rol'=>'encargado-punto-verde'
            ],

            // CUADRILLA LIMPIEZA
            [
                'name'=>'José Ramírez',
                'email'=>'jose@sigrem.com',
                'rol'=>'cuadrilla-limpieza'
            ],
            [
                'name'=>'Pedro Castillo',
                'email'=>'pedro.castillo@sigrem.com',
                'rol'=>'cuadrilla-limpieza'
            ],
            [
                'name'=>'Mario López',
                'email'=>'mario.lopez@sigrem.com',
                'rol'=>'cuadrilla-limpieza'
            ],

            // CIUDADANOS
            [
                'name'=>'Laura Méndez',
                'email'=>'laura@sigrem.com',
                'rol'=>'ciudadano'
            ],
            [
                'name'=>'Andrea Pérez',
                'email'=>'andrea@sigrem.com',
                'rol'=>'ciudadano'
            ],
            [
                'name'=>'Carlos Méndez',
                'email'=>'carlos.mendez@sigrem.com',
                'rol'=>'ciudadano'
            ],

            // AUDITORES
            [
                'name'=>'Fernando Soprano',
                'email'=>'fernando@sigrem.com',
                'rol'=>'auditor'
            ],
            [
                'name'=>'Ricardo Luna',
                'email'=>'ricardo@sigrem.com',
                'rol'=>'auditor'
            ],
            [
                'name'=>'María Estrada',
                'email'=>'maria@sigrem.com',
                'rol'=>'auditor'
            ],

        ];

        foreach ($usuarios as $data) {

            $user = User::firstOrCreate(
            ['email'=>$data['email']],
            [
                'name'=>$data['name'],
                'password'=>Hash::make('password'),
                'estado'=>true,
                'telefono'=>$data['telefono'] ?? '00000000',
                'direccion'=>$data['direccion'] ?? 'Quetzaltenango',
                'nombres'=>$data['nombres'] ?? $data['name'],
                'apellidos'=>$data['apellidos'] ?? 'Usuario',
            ]
        );

            $user->assignRole($data['rol']);

            if ($data['rol'] === 'conductor') {

                Conductor::create([
                    'id_usuario' => $user->id,
                    'licencia' => 'LIC-' . rand(1000,9999),
                    'fecha_vencimiento_licencia' => now()->addYear(),
                    'categoria_licencia' => 'C',
                    'disponible' => true
                ]);

            }

            if ($data['rol'] === 'ciudadano') {

                Ciudadano::create([
                    'id_usuario' => $user->id,
                    'puntos_acumulados' => 0,
                    'nivel' => 1,
                    'logros' => json_encode([]),
                    'preferencias' => json_encode([])
                ]);

            }
        }
    }
}
