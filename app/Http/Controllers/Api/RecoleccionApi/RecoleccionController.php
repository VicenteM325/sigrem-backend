<?php

namespace App\Http\Controllers\Api\RecoleccionApi;

use App\Services\RecoleccionService\RecoleccionService;
use App\DTOs\RecoleccionDTOs\RecoleccionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class RecoleccionController extends ApiController
{
    public function __construct(
        private RecoleccionService $recoleccionService
    ) {}

    /**
     * Listar todas las recolecciones
     * Requiere: recoleccion.ver
     * GET /api/recolecciones
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('recoleccion.ver');

        try {
            $result = $this->recoleccionService->getAllRecolecciones();

            return $this->successResponse(
                $result,
                'Recolecciones obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener recolecciones: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener una recolección específica
     * Requiere: recoleccion.ver
     * GET /api/recolecciones/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.ver');

        try {
            $recoleccion = $this->recoleccionService->getRecoleccionById($id);

            if (!$recoleccion) {
                return $this->notFoundResponse('Recolección no encontrada');
            }

            return $this->successResponse(
                ['recoleccion' => $recoleccion],
                'Recolección obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener recolección: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Iniciar recolección
     * Requiere: recoleccion.iniciar
     * POST /api/recolecciones/{id}/iniciar
     */
    public function iniciar(int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.iniciar');

        try {
            $recoleccion = $this->recoleccionService->iniciarRecoleccion($id);

            return $this->successResponse(
                ['recoleccion' => $recoleccion],
                'Recolección iniciada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al iniciar recolección: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Finalizar recolección
     * Requiere: recoleccion.finalizar
     * POST /api/recolecciones/{id}/finalizar
     */
    public function finalizar(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.finalizar');

        try {
            $request->validate([
                'basura_recolectada' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string|max:500'
            ]);

            \Log::info('=== INTENTANDO FINALIZAR RECOLECCIÓN ===', [
                'id' => $id,
                'basura' => $request->basura_recolectada
            ]);

            $recoleccion = $this->recoleccionService->finalizarRecoleccion(
                $id,
                $request->basura_recolectada,
                $request->observaciones
            );

            return $this->successResponse(
                ['recoleccion' => $recoleccion],
                'Recolección finalizada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error validación finalizar:', ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error('ERROR FINALIZAR RECOLECCIÓN:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Error al finalizar recolección: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Reportar incidencia en recolección
     * Requiere: recoleccion.reportar-incidencia
     * POST /api/recolecciones/{id}/reportar-incidencia
     */
    public function reportarIncidencia(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.reportar-incidencia');

        try {
            $request->validate([
                'observaciones' => 'required|string|max:500',
                'estado' => 'nullable|in:incompleta'
            ]);

            $recoleccion = $this->recoleccionService->reportarIncidencia(
                $id,
                $request->observaciones,
                $request->estado ?? 'incompleta'
            );

            return $this->successResponse(
                ['recoleccion' => $recoleccion],
                'Incidencia reportada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al reportar incidencia: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener recolecciones pendientes
     * Requiere: recoleccion.ver
     * GET /api/recolecciones/pendientes
     */
    public function pendientes(): JsonResponse
    {
        $this->authorizePermission('recoleccion.ver');

        try {
            $recolecciones = $this->recoleccionService->getRecoleccionesPendientes();

            return $this->successResponse(
                ['recolecciones' => $recolecciones],
                'Recolecciones pendientes obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener recolecciones pendientes: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener recolecciones en progreso
     * Requiere: recoleccion.ver
     * GET /api/recolecciones/en-progreso
     */
    public function enProgreso(): JsonResponse
    {
        $this->authorizePermission('recoleccion.ver');

        try {
            $recolecciones = $this->recoleccionService->getRecoleccionesEnProceso();

            return $this->successResponse(
                ['recolecciones' => $recolecciones],
                'Recolecciones en progreso obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener recolecciones en progreso: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener estadísticas de recolección
     * Requiere: reportes.ver
     * GET /api/recolecciones/estadisticas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        $this->authorizePermission('reportes.ver');

        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $estadisticas = $this->recoleccionService->getEstadisticas(
                $request->fecha_inicio,
                $request->fecha_fin
            );

            return $this->successResponse(
                $estadisticas,
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
}
