<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // limpiar cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ROLES
        $superAdmin = Role::firstOrCreate(['name'=>'super-admin','guard_name'=>'web']);
        $admin = Role::firstOrCreate(['name'=>'administrador','guard_name'=>'web']);
        $supervisor = Role::firstOrCreate(['name'=>'supervisor-rutas','guard_name'=>'web']);
        $conductor = Role::firstOrCreate(['name'=>'conductor','guard_name'=>'web']);
        $operador = Role::firstOrCreate(['name'=>'operador-recoleccion','guard_name'=>'web']);
        $encargadoPV = Role::firstOrCreate(['name'=>'encargado-punto-verde','guard_name'=>'web']);
        $cuadrilla = Role::firstOrCreate(['name'=>'cuadrilla-limpieza','guard_name'=>'web']);
        $ciudadano = Role::firstOrCreate(['name'=>'ciudadano','guard_name'=>'web']);
        $auditor = Role::firstOrCreate(['name'=>'auditor','guard_name'=>'web']);

        // SUPER ADMIN → TODO
        $superAdmin->syncPermissions(Permission::all());

        // ADMINISTRADOR
        $admin->syncPermissions([
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.asignar-roles',
            'rutas.ver',
            'rutas.crear',
            'rutas.editar',
            'denuncias.ver',
            'reportes.ver',
            'dashboard.ver',
        ]);

        // SUPERVISOR RUTAS
        $supervisor->syncPermissions([
            'rutas.ver',
            'rutas.planificar',
            'rutas.asignar-camion',
            'recoleccion.ver',
            'reportes.ver',
        ]);

        // CONDUCTOR
        $conductor->syncPermissions([
            'recoleccion.ver',
            'recoleccion.iniciar',
            'recoleccion.finalizar',
            'recoleccion.reportar-incidencia',
        ]);

        // OPERADOR RECOLECCION
        $operador->syncPermissions([
            'recoleccion.ver',
            'recoleccion.reportar-incidencia',
        ]);

        // ENCARGADO PUNTO VERDE
        $encargadoPV->syncPermissions([
            'puntos-verdes.ver',
            'puntos-verdes.crear',
            'puntos-verdes.editar',
            'contenedores.gestionar',
            'entregas.registrar',
        ]);

        // CUADRILLA LIMPIEZA
        $cuadrilla->syncPermissions([
            'recoleccion.ver',
        ]);

        // CIUDADANO
        $ciudadano->syncPermissions([
            'denuncias.crear',
            'denuncias.subir-evidencia',
            'puntos-verdes.ver',
            'rutas.ver',
        ]);

        // AUDITOR
        $auditor->syncPermissions([
            'reportes.ver',
            'reportes.exportar',
            'dashboard.ver',
        ]);
    }
}
