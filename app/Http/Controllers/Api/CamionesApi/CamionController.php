<?php

namespace App\Http\Controllers\Api\CamionesApi;

use App\Services\CamionesService\CamionService;
use App\DTOs\CamionesDTOs\CamionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class CamionController extends ApiController
{
    public function __construct(
        private CamionService $camionService
    ) {}

    /**
     * Listar todos los camiones
     * Requiere: rutas.ver
     * GET /api/camiones
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $result = $this->camionService->getAllCamiones();
            
            return $this->successResponse(
                $result,
                'Camiones obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener camiones: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener un camión específico
     * Requiere: rutas.ver
     * GET /api/camiones/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $camion = $this->camionService->getCamionById($id);
            
            if (!$camion) {
                return $this->notFoundResponse('Camión no encontrado');
            }
            
            return $this->successResponse(
                ['camion' => $camion],
                'Camión obtenido correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nuevo camión
     * Requiere: rutas.crear
     * POST /api/camiones
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.crear');

        try {
            $request->validate([
                'placa' => 'required|string|max:20|unique:camiones,placa',
                'capacidad_toneladas' => 'required|numeric|min:0.1|max:100',
                'id_conductor' => 'nullable|exists:conductors,id_conductor',
                'estado_vehiculo' => 'nullable|in:operativo,mantenimiento,fuera_servicio'
            ]);

            $camionDTO = CamionDTO::fromRequest($request->all());
            
            $camion = $this->camionService->createCamion($camionDTO);
            
            return $this->successResponse(
                ['camion' => $camion],
                'Camión creado correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar camión
     * Requiere: rutas.editar
     * PUT /api/camiones/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'placa' => 'required|string|max:20|unique:camiones,placa,' . $id . ',id_camion',
                'capacidad_toneladas' => 'required|numeric|min:0.1|max:100',
                'id_conductor' => 'nullable|exists:conductors,id_conductor',
                'estado_vehiculo' => 'required|in:operativo,mantenimiento,fuera_servicio'
            ]);

            $camionDTO = CamionDTO::fromRequest($request->all());
            
            $camion = $this->camionService->updateCamion($id, $camionDTO);
            
            return $this->successResponse(
                ['camion' => $camion],
                'Camión actualizado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar camión
     * Requiere: rutas.eliminar
     * DELETE /api/camiones/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.eliminar');

        try {
            $result = $this->camionService->deleteCamion($id);
            
            return $this->successResponse(
                null,
                'Camión eliminado correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener camiones disponibles (sin asignaciones activas)
     * Requiere: rutas.ver
     * GET /api/camiones/disponibles
     */
    public function disponibles(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $camiones = $this->camionService->getCamionesDisponibles();
            
            return $this->successResponse(
                ['camiones' => $camiones],
                'Camiones disponibles obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener camiones disponibles: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener camiones disponibles para una fecha específica
     * Requiere: rutas.ver
     * GET /api/camiones/disponibles-para-fecha/{fecha}
     */
    public function disponiblesParaFecha(string $fecha): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $camiones = $this->camionService->getCamionesDisponiblesParaFecha($fecha);

            return $this->successResponse(
                ['camiones' => $camiones],
                'Camiones disponibles obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener camiones disponibles: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Cambiar estado del camión
     * Requiere: rutas.editar
     * PATCH /api/camiones/{id}/estado
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'estado' => 'required|in:operativo,mantenimiento,fuera_servicio'
            ]);

            $camion = $this->camionService->cambiarEstado($id, $request->estado);
            
            return $this->successResponse(
                ['camion' => $camion],
                'Estado del camión actualizado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al cambiar estado del camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener camiones para selector (uso público en formularios)
     * Requiere: rutas.ver
     * GET /api/camiones/select
     */
    public function forSelect(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $camiones = $this->camionService->getCamionesForSelect();
            
            return $this->successResponse(
                ['camiones' => $camiones],
                'Camiones obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener camiones: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener conductores disponibles para asignar
     * Requiere: rutas.editar
     * GET /api/camiones/conductores-disponibles
     */
    public function conductoresDisponibles(): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $conductores = $this->camionService->getConductoresDisponibles();
        
            return $this->successResponse(
                ['conductores' => $conductores],
                'Conductores disponibles obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener conductores disponibles: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Asignar conductor a camión
     * Requiere: rutas.editar
     * POST /api/camiones/{id}/asignar-conductor
     */
    public function asignarConductor(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'id_conductor' => 'required|exists:conductors,id_conductor'
            ]);

            $camion = $this->camionService->asignarConductor($id, $request->id_conductor);
            
            return $this->successResponse(
                ['camion' => $camion],
                'Conductor asignado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al asignar conductor: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Quitar conductor de camión
     * Requiere: rutas.editar
     * DELETE /api/camiones/{id}/quitar-conductor
     */
    public function quitarConductor(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $camion = $this->camionService->quitarConductor($id);
            
            return $this->successResponse(
                ['camion' => $camion],
                'Conductor removido correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al remover conductor: ' . $e->getMessage(),
                500
            );
        }
    }
}