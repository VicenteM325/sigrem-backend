<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\RecoleccionController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {

    // usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    Route::get('/user', fn(Request $request) => $request->user());

    // Usuarios
    Route::middleware('permission:usuarios.ver')
        ->get('/usuarios', [UserController::class,'index']);

    Route::middleware('permission:usuarios.crear')
        ->post('/usuarios', [UserController::class,'store']);

    // Rutas
    Route::middleware('permission:rutas.ver')
        ->get('/rutas', [RutaController::class,'index']);

    Route::middleware('permission:rutas.crear')
    ->post('/rutas', [RutaController::class,'store']);

    // Denuncias
    Route::middleware('permission:denuncias.ver')
    ->get('/denuncias', [DenunciaController::class,'index']);

    Route::middleware('permission:denuncias.crear')
        ->post('/denuncias', [DenunciaController::class,'store']);
    
});