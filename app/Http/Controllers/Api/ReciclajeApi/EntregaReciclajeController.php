<?php

namespace App\Http\Controllers\Api\ReciclajeApi;

use App\Http\Controllers\Api\ApiController;
use App\Services\ReciclajeService\EntregaReciclajeService;
use App\DTOs\ReciclajeDTOs\EntregaReciclajeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EntregaReciclajeController extends ApiController
{
    public function __construct(
        private EntregaReciclajeService $entregaService
    ) {}

    /**
     * Listar todas las entregas de reciclaje
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            $result = $this->entregaService->getAllEntregas();

            return $this->successResponse(
                $result,
                'Entregas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener entregas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener una entrega específica
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            $entrega = $this->entregaService->getEntregaById($id);

            if (!$entrega) {
                return $this->notFoundResponse('Entrega no encontrada');
            }

            return $this->successResponse(
                ['entrega' => $entrega],
                'Entrega obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener entrega: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nueva entrega de reciclaje
     * Requiere: entregas.registrar
     * POST /api/entregas-reciclaje
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('entregas.registrar');

        try {
            $request->validate([
                'id_punto_verde' => 'required|exists:puntos_verdes,id_punto_verde',
                'id_material' => 'required|exists:tipos_material,id_material',
                'id_ciudadano' => 'required|exists:ciudadanos,id_ciudadano',
                'cantidad_kg' => 'required|numeric|min:0.1|max:100'
            ]);

            $entregaDTO = EntregaReciclajeDTO::fromRequest($request->all());
            $entrega = $this->entregaService->createEntrega($entregaDTO);

            return $this->successResponse(
                ['entrega' => $entrega],
                'Entrega registrada correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al registrar entrega: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar una entrega
     * Requiere: entregas.editar
     * PUT /api/entregas-reciclaje/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('entregas.editar');

        try {
            $request->validate([
                'id_punto_verde' => 'sometimes|exists:puntos_verdes,id_punto_verde',
                'id_material' => 'sometimes|exists:tipos_material,id_material',
                'cantidad_kg' => 'sometimes|numeric|min:0.1|max:100'
            ]);

            // Nota: La actualización de entregas podría estar restringida
            // o requerir lógica adicional para ajustar puntos
            return $this->errorResponse('Operación no permitida', 403);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar una entrega
     * Requiere: entregas.eliminar
     * DELETE /api/entregas-reciclaje/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('entregas.eliminar');

        try {
            // Nota: La eliminación de entregas podría requerir
            // ajustar los puntos del ciudadano
            return $this->errorResponse('Operación no permitida', 403);

        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener entregas por ciudadano
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje/ciudadano/{idCiudadano}
     */
    public function porCiudadano(int $idCiudadano): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            $result = $this->entregaService->getEntregasByCiudadano($idCiudadano);

            return $this->successResponse(
                $result,
                'Entregas del ciudadano obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener entregas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener entregas por punto verde
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje/punto-verde/{idPuntoVerde}
     */
    public function porPuntoVerde(int $idPuntoVerde): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            // Este método requeriría implementación en el servicio
            return $this->successResponse(
                ['entregas' => []],
                'Funcionalidad en desarrollo'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener entregas del día actual
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje/del-dia
     */
    public function delDia(): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            $result = $this->entregaService->getEntregasDelDia();

            return $this->successResponse(
                $result,
                'Entregas del día obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener entregas del día: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener estadísticas de entregas
     * Requiere: reportes.ver
     * GET /api/entregas-reciclaje/estadisticas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        $this->authorizePermission('reportes.ver');

        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            $estadisticas = $this->entregaService->getEstadisticas(
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

    /**
     * Obtener resumen de puntos de un ciudadano
     * Requiere: entregas.ver
     * GET /api/entregas-reciclaje/ciudadano/{idCiudadano}/puntos
     */
    public function puntosCiudadano(int $idCiudadano): JsonResponse
    {
        $this->authorizePermission('entregas.ver');

        try {
            // Usar el CiudadanoRepository directamente si es necesario
            // o agregar método al servicio
            return $this->successResponse(
                ['mensaje' => 'Funcionalidad en desarrollo'],
                'Puntos del ciudadano'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener materiales más reciclados
     * Requiere: reportes.ver
     * GET /api/entregas-reciclaje/materiales-top
     */
    public function materialesTop(Request $request): JsonResponse
    {
        $this->authorizePermission('reportes.ver');

        try {
            $request->validate([
                'limite' => 'sometimes|integer|min:1|max:20'
            ]);

            $limite = $request->get('limite', 5);

            return $this->successResponse(
                ['materiales' => []],
                'Top materiales obtenidos correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}
