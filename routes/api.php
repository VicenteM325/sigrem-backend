<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Api\EspaciosApi\ZonaController;
use App\Http\Controllers\Api\EspaciosApi\PuntoVerdeController;
use App\Http\Controllers\Api\RutasApi\RutaController;
use App\Http\Controllers\Api\RutasApi\EstadoRutaController;
use App\Http\Controllers\Api\CamionesApi\CamionController;
use App\Http\Controllers\Api\RutasApi\AsignacionRutaCamionController;
use App\Http\Controllers\Api\ResiduosApi\TipoResiduoController;
use App\Http\Controllers\Api\RecoleccionApi\RecoleccionController;
use App\Http\Controllers\Api\RecoleccionApi\PuntoRutaController;
use App\Http\Controllers\Api\RecoleccionApi\PuntoRecoleccionBasuraController;
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

     // Camiones
    Route::prefix('camiones')->group(function () {
        Route::get('/disponibles', [CamionController::class, 'disponibles']);
        Route::get('/disponibles-para-fecha/{fecha}', [CamionController::class, 'disponiblesParaFecha']);
        Route::get('/select', [CamionController::class, 'forSelect']);
        Route::get('/conductores-disponibles', [CamionController::class, 'conductoresDisponibles']);
        Route::patch('/{id}/estado', [CamionController::class, 'cambiarEstado']);
        Route::post('/{id}/asignar-conductor', [CamionController::class, 'asignarConductor']);
        Route::delete('/{id}/quitar-conductor', [CamionController::class, 'quitarConductor']);
    });
    Route::apiResource('camiones', CamionController::class);

    // Asignaciones Ruta-Camión
    Route::prefix('asignaciones-ruta-camion')->group(function () {
        Route::get('/pendientes', [AsignacionRutaCamionController::class, 'pendientes']);
        Route::get('/por-fecha/{fecha}', [AsignacionRutaCamionController::class, 'porFecha']);
        Route::get('/calendario', [AsignacionRutaCamionController::class, 'calendario']);
        Route::get('/estadisticas', [AsignacionRutaCamionController::class, 'estadisticas']);
        Route::get('/verificar-disponibilidad', [AsignacionRutaCamionController::class, 'verificarDisponibilidad']);
        Route::patch('/{id}/estado', [AsignacionRutaCamionController::class, 'cambiarEstado']);
    });
    Route::apiResource('asignaciones-ruta-camion', AsignacionRutaCamionController::class);

    // Puntos de Ruta
    Route::prefix('puntos-ruta')->group(function () {
        Route::get('/ruta/{idRuta}', [PuntoRutaController::class, 'porRuta']);
        Route::post('/ruta/{idRuta}', [PuntoRutaController::class, 'guardar']);
    });

     // Puntos de recolección
    Route::prefix('puntos-recoleccion-basura')->group(function () {
        Route::post('/{id}/registrar', [PuntoRecoleccionBasuraController::class, 'registrarBasura']);
        Route::post('/{id}/completar', [PuntoRecoleccionBasuraController::class, 'completarPunto']);
        Route::post('/{id}/problema', [PuntoRecoleccionBasuraController::class, 'reportarProblema']);
    });

    // Recolecciones
    Route::prefix('recolecciones')->group(function () {
        Route::get('/pendientes', [RecoleccionController::class, 'pendientes']);
        Route::get('/en-progreso', [RecoleccionController::class, 'enProgreso']);
        Route::get('/estadisticas', [RecoleccionController::class, 'estadisticas']);
        Route::get('/{idRecoleccion}/puntos', [PuntoRecoleccionBasuraController::class, 'getByRecoleccion']);
        Route::post('/{id}/iniciar', [RecoleccionController::class, 'iniciar']);
        Route::post('/{id}/finalizar', [RecoleccionController::class, 'finalizar']);
        Route::post('/{id}/reportar-incidencia', [RecoleccionController::class, 'reportarIncidencia']);
    });
    Route::apiResource('recolecciones', RecoleccionController::class)->only(['index', 'show']);

    // Puntos Verdes
    Route::prefix('puntos-verdes')->group(function () {
        Route::get('/por-zona/{idZona}', [PuntoVerdeController::class, 'porZona']);
        Route::get('/select', [PuntoVerdeController::class, 'forSelect']);
        Route::get('/mapa', [PuntoVerdeController::class, 'mapa']);
    });
    Route::apiResource('puntos-verdes', PuntoVerdeController::class);
});
