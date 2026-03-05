<?php

namespace App\Http\Controllers\Api\RutasApi;

use App\Services\RutasService\RutaService;
use App\DTOs\RutasDTOs\RutaDTO;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RutaController extends ApiController
{
    public function __construct(
        private RutaService $rutaService
    ) {}

    /**
     * Listar todas las rutas
     * Requiere: rutas.ver
     * GET /api/rutas
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $result = $this->rutaService->getAllRutas();
            
            return $this->successResponse(
                $result,
                'Rutas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener rutas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener una ruta específica
     * Requiere: rutas.ver
     * GET /api/rutas/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $ruta = $this->rutaService->getRutaById($id);
            
            if (!$ruta) {
                return $this->notFoundResponse('Ruta no encontrada');
            }
            
            return $this->successResponse(
                ['ruta' => $ruta],
                'Ruta obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener ruta: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nueva ruta
     * Requiere: rutas.crear
     * POST /api/rutas
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.crear');

        try {
            $request->validate([
                'nombre_ruta' => 'required|string|max:100|unique:rutas,nombre_ruta',
                'descripcion' => 'nullable|string',
                'id_zona' => 'required|exists:zonas,id_zona',
                'coordenada_inicio_lat' => 'required|numeric|between:-90,90',
                'coordenada_inicio_lng' => 'required|numeric|between:-180,180',
                'coordenada_fin_lat' => 'required|numeric|between:-90,90',
                'coordenada_fin_lng' => 'required|numeric|between:-180,180',
                'distancia_km' => 'required|numeric|min:0.1',
                'horario_inicio' => 'required|date_format:H:i',
                'horario_fin' => 'required|date_format:H:i|after:horario_inicio',
                'puntos_intermedios' => 'sometimes|array',
                'puntos_intermedios.*.lat' => 'required|numeric|between:-90,90',
                'puntos_intermedios.*.lng' => 'required|numeric|between:-180,180',
                'dias_recoleccion' => 'required|array|min:1',
                'dias_recoleccion.*' => 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
                'tipos_residuo' => 'required|array|min:1',
                'tipos_residuo.*' => 'exists:tipos_residuo,id_tipo_residuo'
            ]);

            $rutaDTO = RutaDTO::fromRequest($request->all());
            
            $ruta = $this->rutaService->createRuta($rutaDTO);
            
            return $this->successResponse(
                ['ruta' => $ruta],
                'Ruta creada correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear ruta: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar ruta
     * Requiere: rutas.editar
     * PUT /api/rutas/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'nombre_ruta' => 'required|string|max:100|unique:rutas,nombre_ruta,' . $id . ',id_ruta',
                'descripcion' => 'nullable|string',
                'id_zona' => 'required|exists:zonas,id_zona',
                'coordenada_inicio_lat' => 'required|numeric|between:-90,90',
                'coordenada_inicio_lng' => 'required|numeric|between:-180,180',
                'coordenada_fin_lat' => 'required|numeric|between:-90,90',
                'coordenada_fin_lng' => 'required|numeric|between:-180,180',
                'distancia_km' => 'required|numeric|min:0.1',
                'horario_inicio' => 'required|date_format:H:i',
                'horario_fin' => 'required|date_format:H:i|after:horario_inicio',
                'puntos_intermedios' => 'sometimes|array',
                'puntos_intermedios.*.lat' => 'required|numeric|between:-90,90',
                'puntos_intermedios.*.lng' => 'required|numeric|between:-180,180',
                'dias_recoleccion' => 'required|array|min:1',
                'dias_recoleccion.*' => 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
                'tipos_residuo' => 'required|array|min:1',
                'tipos_residuo.*' => 'exists:tipos_residuo,id_tipo_residuo'
            ]);

            $rutaDTO = RutaDTO::fromRequest($request->all());
            
            $ruta = $this->rutaService->updateRuta($id, $rutaDTO);
            
            return $this->successResponse(
                ['ruta' => $ruta],
                'Ruta actualizada correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar ruta: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar ruta
     * Requiere: rutas.eliminar
     * DELETE /api/rutas/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('rutas.eliminar');

        try {
            $result = $this->rutaService->deleteRuta($id);
            
            return $this->successResponse(
                null,
                'Ruta eliminada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar ruta: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener rutas para el mapa (público o con permisos)
     * Requiere: rutas.ver
     * GET /api/rutas/mapa
     */
    public function mapa(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $geojson = $this->rutaService->getRutasParaMapa();
            
            return response()->json($geojson);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener rutas para mapa: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Cambiar estado de una ruta
     * Requiere: rutas.editar
     * PATCH /api/rutas/{id}/estado
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.editar');

        try {
            $request->validate([
                'id_estado' => 'required|exists:estados_ruta,id_estado_ruta'
            ]);

            $ruta = $this->rutaService->cambiarEstado($id, $request->id_estado);
            
            return $this->successResponse(
                ['ruta' => $ruta],
                'Estado actualizado correctamente'
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
     * Asignar camión a ruta
     * Requiere: rutas.asignar-camion
     * POST /api/rutas/{id}/asignar-camion
     */
    public function asignarCamion(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.asignar-camion');

        try {
            $request->validate([
                'id_camion' => 'required|exists:camiones,id_camion',
                'fecha' => 'required|date|after:today'
            ]);

            // Aquí iría la lógica de asignación (pendiente de implementar)
            
            return $this->successResponse(
                null,
                'Camión asignado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al asignar camión: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Planificar ruta
     * Requiere: rutas.planificar
     * POST /api/rutas/{id}/planificar
     */
    public function planificar(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.planificar');

        try {
            // Aquí iría la lógica de planificación (pendiente de implementar)
            
            return $this->successResponse(
                null,
                'Ruta planificada correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al planificar ruta: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Buscar rutas
     * Requiere: rutas.ver
     * GET /api/rutas/buscar?q={termino}
     */
    public function buscar(Request $request): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $request->validate([
                'q' => 'required|string|min:2'
            ]);

            $rutas = $this->rutaService->searchRutas($request->q);
            
            return $this->successResponse(
                ['rutas' => $rutas],
                'Búsqueda completada'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al buscar rutas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener rutas por zona
     * Requiere: rutas.ver
     * GET /api/rutas/por-zona/{zonaId}
     */
    public function porZona(int $zonaId): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $rutas = $this->rutaService->getRutasByZona($zonaId);
            
            return $this->successResponse(
                ['rutas' => $rutas],
                'Rutas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener rutas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Duplicar una ruta
     * Requiere: rutas.crear
     * POST /api/rutas/{id}/duplicar
     */
    public function duplicar(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('rutas.crear');

        try {
            $request->validate([
                'nombre' => 'required|string|max:100|unique:rutas,nombre_ruta'
            ]);

            $ruta = $this->rutaService->duplicarRuta($id, $request->nombre);
            
            return $this->successResponse(
                ['ruta' => $ruta],
                'Ruta duplicada correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Error de validación'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al duplicar ruta: ' . $e->getMessage(),
                500
            );
        }
    }
}