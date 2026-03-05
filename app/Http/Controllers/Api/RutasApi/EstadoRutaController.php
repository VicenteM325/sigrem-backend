<?php

namespace App\Http\Controllers\Api\RutasApi;

use App\Repositories\EstadoRutaRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class EstadoRutaController extends ApiController
{
    public function __construct(
        private EstadoRutaRepository $estadoRutaRepository
    ) {}

    /**
     * Listar todos los estados de ruta
     * Requiere: rutas.ver
     * GET /api/estados-ruta
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $estados = $this->estadoRutaRepository->all();
            
            $data = $estados->map(fn($estado) => [
                'id' => $estado->id_estado_ruta,
                'nombre' => $estado->nombre,
                'descripcion' => $estado->descripcion
            ]);
            
            return $this->successResponse(
                ['estados' => $data],
                'Estados obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener estados: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener estado activo por defecto
     * Requiere: rutas.ver
     * GET /api/estados-ruta/activo
     */
    public function activo(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $estado = $this->estadoRutaRepository->getActivo();
            
            if (!$estado) {
                return $this->notFoundResponse('No se encontró estado activo');
            }
            
            return $this->successResponse([
                'estado' => [
                    'id' => $estado->id_estado_ruta,
                    'nombre' => $estado->nombre
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener estado activo: ' . $e->getMessage(),
                500
            );
        }
    }
}