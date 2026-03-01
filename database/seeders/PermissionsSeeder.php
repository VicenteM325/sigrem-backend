<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'usuarios.asignar-roles',

            // Rutas
            'rutas.ver',
            'rutas.crear',
            'rutas.editar',
            'rutas.eliminar',
            'rutas.asignar-camion',
            'rutas.planificar',

            // Recoleccion
            'recoleccion.ver',
            'recoleccion.iniciar',
            'recoleccion.finalizar',
            'recoleccion.reportar-incidencia',

            // Puntos Verdes
            'puntos-verdes.ver',
            'puntos-verdes.crear',
            'puntos-verdes.editar',
            'contenedores.gestionar',
            'entregas.registrar',

            // Denuncias
            'denuncias.ver',
            'denuncias.crear',
            'denuncias.asignar',
            'denuncias.resolver',
            'denuncias.subir-evidencia',

            // Reportes
            'reportes.ver',
            'reportes.exportar',
            'dashboard.ver',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
