<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Api\EspaciosApi\ZonaController;
use App\Http\Controllers\Api\RutasApi\RutaController;
use App\Http\Controllers\Api\RutasApi\EstadoRutaController;
use App\Http\Controllers\Api\ResiduosApi\TipoResiduoController;
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

     // ============ MÓDULO DE RUTAS ============
    // Zonas
    Route::get('/zonas/select', [ZonaController::class, 'forSelect']);
    Route::apiResource('zonas', ZonaController::class);

    // Tipos de residuo
    Route::get('/tipos-residuo/select', [TipoResiduoController::class, 'forSelect']);
    Route::apiResource('tipos-residuo', TipoResiduoController::class)->only(['index']);

    // Estados de ruta
    Route::get('/estados-ruta/activo', [EstadoRutaController::class, 'activo']);
    Route::apiResource('estados-ruta', EstadoRutaController::class)->only(['index']);

    // Rutas
    Route::prefix('rutas')->group(function () {
        Route::get('/mapa', [RutaController::class, 'mapa']);
        Route::get('/buscar', [RutaController::class, 'buscar']);
        Route::get('/por-zona/{zonaId}', [RutaController::class, 'porZona']);
        Route::patch('/{id}/estado', [RutaController::class, 'cambiarEstado']);
        Route::post('/{id}/duplicar', [RutaController::class, 'duplicar']);
        Route::post('/{id}/asignar-camion', [RutaController::class, 'asignarCamion']);
        Route::post('/{id}/planificar', [RutaController::class, 'planificar']);
    });
    Route::apiResource('rutas', RutaController::class);
});