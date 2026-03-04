<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\RecoleccionController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {

    // usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ============ ROLES Y PERMISOS ============
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::get('/permisos', [RoleController::class, 'permissions']);

    // ============ USUARIOS ============
    Route::middleware('permission:usuarios.ver')
        ->get('/usuarios', [UserController::class, 'index']);

    Route::middleware('permission:usuarios.crear')
        ->post('/usuarios', [UserController::class, 'store']);

    // CORREGIDO: Agregar /usuarios/ al path
    Route::middleware('permission:usuarios.ver')
        ->get('/usuarios/{id}', [UserController::class, 'show']);
        
    Route::middleware('permission:usuarios.editar')
        ->put('/usuarios/{id}', [UserController::class, 'update']);
        
    Route::middleware('permission:usuarios.eliminar')
        ->delete('/usuarios/{id}', [UserController::class, 'destroy']);

    // ============ RUTAS ============
    Route::middleware('permission:rutas.ver')
        ->get('/rutas', [RutaController::class, 'index']);

    Route::middleware('permission:rutas.crear')
        ->post('/rutas', [RutaController::class, 'store']);

    // ============ DENUNCIAS ============
    Route::middleware('permission:denuncias.ver')
        ->get('/denuncias', [DenunciaController::class, 'index']);

    Route::middleware('permission:denuncias.crear')
        ->post('/denuncias', [DenunciaController::class, 'store']);
});