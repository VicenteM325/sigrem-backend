<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Obtener todos los roles
     */
    public function index()
    {
        $roles = Role::all()->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')
            ];
        });

        return response()->json([
            'roles' => $roles
        ]);
    }

    /**
     * Obtener un rol específico
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')
            ]
        ]);
    }

    /**
     * Obtener todos los permisos
     */
    public function permissions()
    {
        $permissions = Permission::all()->pluck('name');
        
        return response()->json([
            'permissions' => $permissions
        ]);
    }
}