<?php

namespace App\Http\Controllers\Api\ContenedoresApi;

use App\Services\ContenedoresService\ContenedorService;
use App\DTOs\ContenedoresDTOs\ContenedorDTO;
use App\DTOs\ContenedoresDTOs\VaciadoContenedorDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class ContenedorController extends ApiController
{
    public function __construct(
        private ContenedorService $contenedorService
    ) {}

    /**
     * Listar todos los contenedores
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $result = $this->contenedorService->getAllContenedores();
            return $this->successResponse($result);
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar un contenedor específico
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $contenedor = $this->contenedorService->getContenedorById($id);
            if (!$contenedor) {
                return $this->notFoundResponse('Contenedor no encontrado');
            }
            return $this->successResponse(['contenedor' => $contenedor]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear nuevo contenedor
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.crear');

        try {
            $request->validate([
                'id_punto_verde' => 'required|exists:puntos_verdes,id_punto_verde',
                'id_material' => 'required|exists:tipos_material,id_material',
                'capacidad_m3' => 'required|numeric|min:0.1|max:100',
                'estado_contenedor' => 'nullable|in:disponible,lleno,mantenimiento'
            ]);

            $contenedorDTO = ContenedorDTO::fromRequest($request->all());
            $contenedor = $this->contenedorService->createContenedor($contenedorDTO);

            return $this->successResponse(
                ['contenedor' => $contenedor],
                'Contenedor creado correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar contenedor
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.editar');

        try {
            $request->validate([
                'id_punto_verde' => 'required|exists:puntos_verdes,id_punto_verde',
                'id_material' => 'required|exists:tipos_material,id_material',
                'capacidad_m3' => 'required|numeric|min:0.1|max:100',
                'estado_contenedor' => 'required|in:disponible,lleno,mantenimiento'
            ]);

            $contenedorDTO = ContenedorDTO::fromRequest($request->all());
            $contenedor = $this->contenedorService->updateContenedor($id, $contenedorDTO);

            return $this->successResponse(
                ['contenedor' => $contenedor],
                'Contenedor actualizado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar contenedor
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.eliminar');

        try {
            $this->contenedorService->deleteContenedor($id);
            return $this->successResponse(null, 'Contenedor eliminado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Programar vaciado de contenedor
     */
    public function programarVaciado(Request $request): JsonResponse
    {
        $this->authorizePermission('contenedores.gestionar');

        try {
            $request->validate([
                'id_contenedor' => 'required|exists:contenedores,id_contenedor',
                'fecha_programada' => 'required|date|after_or_equal:today'
            ]);

            $vaciadoDTO = VaciadoContenedorDTO::fromRequest($request->all());
            $vaciado = $this->contenedorService->programarVaciado($vaciadoDTO);

            return $this->successResponse(
                ['vaciado' => $vaciado],
                'Vaciado programado correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Realizar vaciado de contenedor
     */
    public function realizarVaciado(int $id): JsonResponse
    {
        $this->authorizePermission('contenedores.gestionar');

        try {
            $vaciado = $this->contenedorService->realizarVaciado($id);
            return $this->successResponse(
                ['vaciado' => $vaciado],
                'Vaciado realizado correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener vaciados pendientes
     */
    public function vaciadosPendientes(): JsonResponse
    {
        $this->authorizePermission('contenedores.gestionar');

        try {
            $vaciados = $this->contenedorService->getVaciadosPendientes();
            return $this->successResponse(['vaciados' => $vaciados]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener contenedores por llenar (alerta)
     */
    public function porLlenar(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $contenedores = $this->contenedorService->getContenedoresPorLlenar();
            return $this->successResponse(['contenedores' => $contenedores]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}
