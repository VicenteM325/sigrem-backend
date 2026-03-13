<?php

namespace App\Http\Controllers\Api\EspaciosApi;

use App\Services\EspaciosService\PuntoVerdeService;
use App\Services\UserService;
use App\DTOs\EspaciosDTOs\PuntoVerdeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;

class PuntoVerdeController extends ApiController
{
    public function __construct(
        private PuntoVerdeService $puntoVerdeService,
        private UserService $userService
    ) {}

    /**
     * Listar todos los puntos verdes
     * Requiere: puntos-verdes.ver
     * GET /api/puntos-verdes
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $result = $this->puntoVerdeService->getAllPuntosVerdes();

            return $this->successResponse(
                $result,
                'Puntos verdes obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener puntos verdes: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener un punto verde específico
     * Requiere: puntos-verdes.ver
     * GET /api/puntos-verdes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $punto = $this->puntoVerdeService->getPuntoVerdeById($id);

            if (!$punto) {
                return $this->notFoundResponse('Punto verde no encontrado');
            }

            return $this->successResponse(
                ['punto_verde' => $punto],
                'Punto verde obtenido correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener punto verde: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nuevo punto verde
     * Requiere: puntos-verdes.crear
     * POST /api/puntos-verdes
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.crear');

        try {
            $request->validate([
                'id_zona' => 'required|exists:zonas,id_zona',
                'nombre' => 'required|string|max:100',
                'direccion' => 'required|string|max:255',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'capacidad_total_m3' => 'required|numeric|min:0',
                'horario_atencion' => 'required|string|max:100',
                'id_encargado' => 'required|exists:users,id'
            ]);

            $puntoDTO = PuntoVerdeDTO::fromRequest($request->all());

            $punto = $this->puntoVerdeService->createPuntoVerde($puntoDTO);

            return $this->successResponse(
                ['punto_verde' => $punto],
                'Punto verde creado correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear punto verde: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar punto verde
     * Requiere: puntos-verdes.editar
     * PUT /api/puntos-verdes/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.editar');

        try {
            $request->validate([
                'id_zona' => 'required|exists:zonas,id_zona',
                'nombre' => 'required|string|max:100',
                'direccion' => 'required|string|max:255',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'capacidad_total_m3' => 'required|numeric|min:0',
                'horario_atencion' => 'required|string|max:100',
                'id_encargado' => 'required|exists:users,id'
            ]);

            $puntoDTO = PuntoVerdeDTO::fromRequest($request->all());

            $punto = $this->puntoVerdeService->updatePuntoVerde($id, $puntoDTO);

            return $this->successResponse(
                ['punto_verde' => $punto],
                'Punto verde actualizado correctamente'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar punto verde: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Eliminar punto verde
     * Requiere: puntos-verdes.eliminar
     * DELETE /api/puntos-verdes/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.eliminar');

        try {
            $this->puntoVerdeService->deletePuntoVerde($id);

            return $this->successResponse(
                null,
                'Punto verde eliminado correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar punto verde: ' . $e->getMessage(),
                500
            );
        }
    }

     /**
     * Obtener usuarios con rol de encargado de punto verde
     * GET /api/puntos-verdes/encargados-disponibles
     */
    public function encargadosDisponibles(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.editar');

        try {
            $users = $this->userService->getAllUsers();

            $encargadosAsignados = \App\Models\espacios\PuntoVerde::whereNotNull('id_encargado')
                ->pluck('id_encargado')
                ->toArray();

            $encargadosDisponibles = collect($users)
                ->filter(function ($user) use ($encargadosAsignados) {
                    $roles = $user['roles'];
                    if ($roles instanceof \Illuminate\Support\Collection) {
                        $roles = $roles->toArray();
                    }

                    return in_array('encargado-punto-verde', $roles) &&
                           $user['estado'] &&
                           !in_array($user['id'], $encargadosAsignados);
                })
                ->map(function ($user) {
                    return [
                        'value' => $user['id'],
                        'label' => $user['nombres'] . ' ' . $user['apellidos'] . ' (' . $user['email'] . ')'
                    ];
                })
                ->values()
                ->toArray();

            return $this->successResponse(
                ['encargados' => $encargadosDisponibles],
                'Encargados disponibles obtenidos correctamente'
            );
        } catch (\Exception $e) {
            \Log::error('Error en encargadosDisponibles: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener encargados disponibles', 500);
        }
    }

    /**
     * Método helper para obtener datos completos del encargado
     * GET /api/puntos-verdes/encargado/{id}
     */
    public function getEncargadoInfo(int $id): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->notFoundResponse('Encargado no encontrado');
            }

            // Verificar que tiene el rol correcto
            if (!in_array('encargado-punto-verde', $user['roles'])) {
                return $this->errorResponse('El usuario no es un encargado de punto verde', 403);
            }

            return $this->successResponse(
                ['encargado' => $user],
                'Información del encargado obtenida correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener encargado: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener puntos verdes por zona
     * Requiere: puntos-verdes.ver
     * GET /api/puntos-verdes/por-zona/{idZona}
     */
    public function porZona(int $idZona): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $puntos = $this->puntoVerdeService->getPuntosVerdesByZona($idZona);

            return $this->successResponse(
                ['puntos' => $puntos],
                'Puntos verdes por zona obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener puntos verdes por zona: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener puntos verdes para selector
     * GET /api/puntos-verdes/select
     */
    public function forSelect(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $puntos = $this->puntoVerdeService->getPuntosVerdesForSelect();

            return $this->successResponse(
                ['puntos' => $puntos],
                'Puntos verdes obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener puntos verdes: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener coordenadas de puntos verdes para mapa
     * GET /api/puntos-verdes/mapa
     */
    public function mapa(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');

        try {
            $coordenadas = $this->puntoVerdeService->getCoordenadas();

            return $this->successResponse(
                ['puntos' => $coordenadas],
                'Coordenadas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener coordenadas: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener todos los puntos verdes para el mapa
     * GET /api/puntos-verdes/mapa/completo
     */
    public function getPuntosVerdesMapa(): JsonResponse
    {
        $this->authorizePermission('puntos-verdes.ver');
        try {
            $puntos = $this->puntoVerdeService->getPuntosVerdesMapa();
            return $this->successResponse(
                ['puntos' => $puntos],
                'Puntos verdes obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener puntos verdes: ' . $e->getMessage(), 500);
        }
    }
}
