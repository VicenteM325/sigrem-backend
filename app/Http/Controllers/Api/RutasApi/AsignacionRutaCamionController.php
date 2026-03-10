<?php

namespace App\Http\Controllers\Api\RutasApi;

use App\Services\RutasService\AsignacionRutaCamionService;
use App\DTOs\RutasDTOs\AsignacionRutaCamionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class AsignacionRutaCamionController extends ApiController
{
    public function __construct(
        private AsignacionRutaCamionService $asignacionService
    ) {}

    /**
     * Listar todas las asignaciones
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $filtros = $request->only(['fecha', 'fecha_inicio', 'fecha_fin', 'estado', 'id_camion', 'id_ruta']);
            $result = $this->asignacionService->getAllAsignaciones($filtros);
            
            return $this->successResponse(
                $result,
                'Asignaciones obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener asignaciones: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener una asignación específica
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $asignacion = $this->asignacionService->getAsignacionById($id);
            
            if (!$asignacion) {
                return $this->notFoundResponse('Asignación no encontrada');
            }
            
            return $this->successResponse(
                ['asignacion' => $asignacion],
                'Asignación obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener asignación: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nueva asignación
     * Requiere: rutas.asignar-camion
     * POST /api/asignaciones-ruta-camion
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.asignar-camion');

        try {
            $request->validate([
                'id_ruta' => 'required|exists:rutas,id_ruta',
                'id_camion' => 'required|exists:camiones,id_camion',
                'fecha_programada' => 'required|date|after_or_equal:today',
                'total_estimado_kg' => 'nullable|numeric|min:0'
            ]);

            $asignacionDTO = AsignacionRutaCamionDTO::fromRequest($request->all());
            
            $asignacion = $this->asignacionService->createAsignacion($asignacionDTO);
            
            return $this->successResponse(
                ['asignacion' => $asignacion],
                'Asignación creada correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear asignación: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar asignación
     * Requiere: rutas.asignar-camion
     * PUT /api/asignaciones-ruta-camion/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.asignar-camion');

        try {
            $request->validate([
                'id_ruta' => 'sometimes|required|exists:rutas,id_ruta',
                'id_camion' => 'sometimes|required|exists:camiones,id_camion',
                'fecha_programada' => 'sometimes|required|date',
                'total_estimado_kg' => 'nullable|numeric|min:0'
            ]);

            $asignacionDTO = AsignacionRutaCamionDTO::fromRequest($request->all());
            
            $asignacion = $this->asignacionService->updateAsignacion($id, $asignacionDTO);
            
            return $this->successResponse(
                ['asignacion' => $asignacion],
                'Asignación actualizada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar asignación: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Cambiar estado de la asignación
     * Requiere: rutas.planificar
     * PATCH /api/asignaciones-ruta-camion/{id}/estado
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.planificar');

        try {
            $request->validate([
                'estado' => 'required|in:programada,en_proceso,completada,cancelada',
                'total_real_kg' => 'required_if:estado,completada|numeric|min:0|nullable'
            ]);

            $datosAdicionales = $request->only(['total_real_kg']);
            $asignacion = $this->asignacionService->cambiarEstado($id, $request->estado, $datosAdicionales);
            
            return $this->successResponse(
                ['asignacion' => $asignacion],
                'Estado de asignación actualizado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al cambiar estado: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar asignación
     * Requiere: rutas.eliminar
     * DELETE /api/asignaciones-ruta-camion/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.eliminar');

        try {
            $result = $this->asignacionService->deleteAsignacion($id);
            
            return $this->successResponse(
                null,
                'Asignación eliminada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar asignación: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener asignaciones pendientes
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/pendientes
     */
    public function pendientes(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $result = $this->asignacionService->getAsignacionesPendientes();
            
            return $this->successResponse(
                $result,
                'Asignaciones pendientes obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener asignaciones pendientes: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener asignaciones por fecha
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/por-fecha/{fecha}
     */
    public function porFecha(string $fecha): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $result = $this->asignacionService->getAsignacionesByFecha($fecha);
            
            return $this->successResponse(
                $result,
                'Asignaciones obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener asignaciones: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener calendario de asignaciones
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/calendario
     */
    public function calendario(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $result = $this->asignacionService->getCalendario(
                $request->fecha_inicio,
                $request->fecha_fin
            );
            
            return $this->successResponse(
                $result,
                'Calendario obtenido correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener calendario: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener estadísticas de asignaciones
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/estadisticas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $result = $this->asignacionService->getEstadisticas(
                $request->fecha_inicio,
                $request->fecha_fin
            );
            
            return $this->successResponse(
                $result,
                'Estadísticas obtenidas correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener estadísticas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Verificar disponibilidad de camión
     * Requiere: rutas.ver
     * GET /api/asignaciones-ruta-camion/verificar-disponibilidad
     */
    public function verificarDisponibilidad(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $request->validate([
                'id_camion' => 'required|exists:camiones,id_camion',
                'fecha' => 'required|date'
            ]);

            $result = $this->asignacionService->verificarDisponibilidadCamion(
                $request->id_camion,
                $request->fecha
            );
            
            return $this->successResponse(
                $result,
                'Disponibilidad verificada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al verificar disponibilidad: ' . $e->getMessage(),
                500
            );
        }
    }
}