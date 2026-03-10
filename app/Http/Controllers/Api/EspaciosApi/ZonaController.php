<?php

namespace App\Http\Controllers\Api\EspaciosApi;

use App\Services\EspaciosService\ZonaService;
use App\DTOs\EspaciosDTOs\ZonaDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class ZonaController extends ApiController
{
    public function __construct(
        private ZonaService $zonaService
    ) {}

    /**
     * Listar todas las zonas
     * Requiere: rutas.ver (ya que zonas están asociadas a rutas)
     * GET /api/zonas
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $result = $this->zonaService->getAllZonas();
            
            return $this->successResponse(
                $result,
                'Zonas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener zonas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener una zona específica
     * Requiere: rutas.ver
     * GET /api/zonas/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $zona = $this->zonaService->getZonaById($id);
            
            if (!$zona) {
                return $this->notFoundResponse('Zona no encontrada');
            }
            
            return $this->successResponse(
                ['zona' => $zona],
                'Zona obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener zona: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nueva zona
     * Requiere: rutas.crear (crear rutas implica crear zonas)
     * POST /api/zonas
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.crear');

        try {
            $request->validate([
                'nombre_zona' => 'required|string|max:100',
                'densidad_poblacional' => 'nullable|numeric|min:0|max:5',
                'coordenadas_poligono' => 'nullable|array',
                'tipos_zona' => 'nullable|array'
            ]);

            $zonaDTO = ZonaDTO::fromRequest($request->all());
            
            $zona = $this->zonaService->createZona($zonaDTO);
            
            return $this->successResponse(
                ['zona' => $zona],
                'Zona creada correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear zona: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar zona
     * Requiere: rutas.editar
     * PUT /api/zonas/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'nombre_zona' => 'required|string|max:100',
                'densidad_poblacional' => 'nullable|numeric|min:0|max:5',
                'coordenadas_poligono' => 'nullable|array',
                'tipos_zona' => 'nullable|array'
            ]);

            $zonaDTO = ZonaDTO::fromRequest($request->all());
            
            $zona = $this->zonaService->updateZona($id, $zonaDTO);
            
            return $this->successResponse(
                ['zona' => $zona],
                'Zona actualizada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar zona: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar zona
     * Requiere: rutas.eliminar
     * DELETE /api/zonas/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.eliminar');

        try {
            $result = $this->zonaService->deleteZona($id);
            
            return $this->successResponse(
                null,
                'Zona eliminada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar zona: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener zonas para selector (uso público en formularios)
     * Requiere: rutas.ver
     * GET /api/zonas/select
     */
    public function forSelect(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $zonas = $this->zonaService->getZonasForSelect();
            
            return $this->successResponse(
                ['zonas' => $zonas],
                'Zonas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener zonas: ' . $e->getMessage(),
                500
            );
        }
    }
}