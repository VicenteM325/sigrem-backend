<?php

namespace App\Services\EspaciosService;

use App\DTOs\EspaciosDTOs\PuntoVerdeDTO;
use App\Repositories\PuntoVerdeRepository;
use App\Repositories\ZonaRepository;
use App\Services\BaseService;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Support\Collection;

class PuntoVerdeService extends BaseService
{
    public function __construct(
        private PuntoVerdeRepository $puntoVerdeRepository,
        private ZonaRepository $zonaRepository,
        private UserService $userService
    ) {}

    /**
     * Validar que un usuario es encargado de punto verde
     */
    private function validarEncargado(?int $idEncargado): void
    {
        if (!$idEncargado) {
            return;
        }

        $userData = $this->userService->findUserById($idEncargado);
        if (!$userData) {
            throw new \Exception('El encargado especificado no existe');
        }

        // Convertir roles a array si es colección
        $roles = $userData['roles'];
        if ($roles instanceof Collection) {
            $rolesArray = $roles->toArray();
        } else {
            $rolesArray = (array) $roles;
        }

        if (!in_array('encargado-punto-verde', $rolesArray)) {
            throw new \Exception('El usuario seleccionado no tiene el rol de encargado de punto verde');
        }

        if (!($userData['estado'] ?? true)) {
            throw new \Exception('El encargado seleccionado no está activo');
        }
    }

    /**
     * Obtener todos los puntos verdes
     */
    public function getAllPuntosVerdes(): array
    {
        $puntos = $this->puntoVerdeRepository->getAllWithZona();

        return [
            'puntos' => $puntos->map(fn($punto) => PuntoVerdeDTO::fromModel($punto)->toResponseArray()),
            'total' => $puntos->count()
        ];
    }

    /**
     * Obtener punto verde por ID
     */
    public function getPuntoVerdeById(int $id): ?array
    {
        $punto = $this->puntoVerdeRepository->findWithRelations($id);

        if (!$punto) {
            return null;
        }

        return PuntoVerdeDTO::fromModel($punto)->toResponseArray();
    }

    /**
     * Crear nuevo punto verde
     */
    public function createPuntoVerde(PuntoVerdeDTO $puntoVerdeDTO): array
    {
        return $this->executeInTransaction(function () use ($puntoVerdeDTO) {
            // Validar que la zona existe
            $zona = $this->zonaRepository->find($puntoVerdeDTO->id_zona);
            if (!$zona) {
                throw new \Exception('La zona especificada no existe');
            }

            // Validar encargado
            $this->validarEncargado($puntoVerdeDTO->id_encargado);

            // Crear el punto verde
            $punto = $this->puntoVerdeRepository->create($puntoVerdeDTO->toArray());

            $this->logInfo('Punto verde creado', [
                'id' => $punto->id_punto_verde,
                'nombre' => $punto->nombre
            ]);

            return PuntoVerdeDTO::fromModel($punto->fresh('zona', 'encargado'))->toResponseArray();
        });
    }

    /**
     * Actualizar punto verde
     */
    public function updatePuntoVerde(int $id, PuntoVerdeDTO $puntoVerdeDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $puntoVerdeDTO) {
            $punto = $this->puntoVerdeRepository->find($id);

            if (!$punto) {
                throw new \Exception('Punto verde no encontrado');
            }

            // Validar zona si cambió
            if ($puntoVerdeDTO->id_zona !== $punto->id_zona) {
                $zona = $this->zonaRepository->find($puntoVerdeDTO->id_zona);
                if (!$zona) {
                    throw new \Exception('La zona especificada no existe');
                }
            }

            // Validar encargado si cambió
            if ($puntoVerdeDTO->id_encargado !== $punto->id_encargado) {
                $this->validarEncargado($puntoVerdeDTO->id_encargado);
            }

            // Actualizar punto verde
            $punto = $this->puntoVerdeRepository->update($punto, $puntoVerdeDTO->toArray());

            $this->logInfo('Punto verde actualizado', ['id' => $id]);

            return PuntoVerdeDTO::fromModel($punto->fresh('zona', 'encargado'))->toResponseArray();
        });
    }

    /**
     * Eliminar punto verde
     */
    public function deletePuntoVerde(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $punto = $this->puntoVerdeRepository->find($id);

            if (!$punto) {
                throw new \Exception('Punto verde no encontrado');
            }

            $result = $this->puntoVerdeRepository->delete($punto);

            $this->logInfo('Punto verde eliminado', ['id' => $id]);

            return $result;
        });
    }

    /**
     * Obtener puntos verdes por zona
     */
    public function getPuntosVerdesByZona(int $idZona): array
    {
        $puntos = $this->puntoVerdeRepository->getByZona($idZona);

        return $puntos->map(fn($punto) => PuntoVerdeDTO::fromModel($punto)->toResponseArray())->toArray();
    }

    /**
     * Obtener puntos verdes para selector
     */
    public function getPuntosVerdesForSelect(): array
    {
        return $this->puntoVerdeRepository->getForSelect();
    }

    /**
     * Obtener coordenadas de todos los puntos verdes (para mapa)
     */
    public function getCoordenadas(): array
    {
        return $this->puntoVerdeRepository->getCoordenadas()
            ->map(fn($punto) => [
                'id' => $punto->id_punto_verde,
                'nombre' => $punto->nombre,
                'lat' => $punto->latitud,
                'lng' => $punto->longitud
            ])
            ->toArray();
    }

    /**
     * Obtener información detallada de los encargados
     */
    public function getEncargadosInfo(): array
    {
        $puntos = $this->puntoVerdeRepository->getAllWithZona();

        $encargadosInfo = [];
        foreach ($puntos as $punto) {
            if ($punto->encargado) {
                $encargadosInfo[] = [
                    'punto_verde' => $punto->nombre,
                    'encargado' => [
                        'id' => $punto->encargado->id,
                        'nombre' => $punto->encargado->nombres . ' ' . $punto->encargado->apellidos,
                        'email' => $punto->encargado->email,
                        'telefono' => $punto->encargado->telefono
                    ]
                ];
            }
        }

        return $encargadosInfo;
    }

    /**
    * Obtener todos los puntos verdes para el mapa
    */
    public function getPuntosVerdesMapa(): array
    {
        $puntos = $this->puntoVerdeRepository->getAllWithZona();

        return $puntos->map(function ($punto) {
            return [
                'id' => $punto->id_punto_verde,
                'nombre' => $punto->nombre,
                'direccion' => $punto->direccion,
                'ubicacion' => [
                    'lat' => $punto->latitud,
                    'lng' => $punto->longitud
                ],
                'capacidad' => $punto->capacidad_total_m3,
                'horario' => $punto->horario_atencion,
                'encargado' => $punto->encargado ? [
                    'id' => $punto->encargado->id,
                    'nombre' => $punto->encargado->nombres . ' ' . $punto->encargado->apellidos
                ] : null,
                'zona' => [
                    'id' => $punto->zona->id_zona,
                    'nombre' => $punto->zona->nombre_zona
                ]
            ];
        })->toArray();
    }
}
