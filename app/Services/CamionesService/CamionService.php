<?php

namespace App\Services\CamionesService;

use App\DTOs\CamionesDTOs\CamionDTO;
use App\Repositories\CamionRepository;
use App\Repositories\ConductorRepository;
use App\Services\BaseService;

class CamionService extends BaseService
{
    public function __construct(
        private CamionRepository $camionRepository,
        private ConductorRepository $conductorRepository 
    ) {}

    /**
     * Obtener todos los camiones
     */
    public function getAllCamiones(): array
    {
        $camiones = $this->camionRepository->getAllWithConductor();
        
        return [
            'camiones' => $camiones->map(fn($camion) => CamionDTO::fromModel($camion)->toResponseArray()),
            'total' => $camiones->count()
        ];
    }

    /**
     * Obtener camión por ID
     */
    public function getCamionById(int $id): ?array
    {
        $camion = $this->camionRepository->findWithRelations($id);
        
        if (!$camion) {
            return null;
        }
        
        return CamionDTO::fromModel($camion)->toResponseArray();
    }

    /**
     * Crear nuevo camión
     */
    public function createCamion(CamionDTO $camionDTO): array
    {
        return $this->executeInTransaction(function () use ($camionDTO) {
            // Validar placa única
            if ($this->camionRepository->findByPlaca($camionDTO->placa)) {
                throw new \Exception('Ya existe un camión con esta placa');
            }

            // Validar conductor si se proporciona
            if ($camionDTO->id_conductor) {
                $conductor = $this->conductorRepository->find($camionDTO->id_conductor);
                if (!$conductor) {
                    throw new \Exception('El conductor especificado no existe');
                }
                
                // Verificar si el conductor ya tiene un camión asignado
                $camionesConductor = $this->camionRepository->getCamionesPorConductor($camionDTO->id_conductor);
                if ($camionesConductor->isNotEmpty()) {
                    throw new \Exception('El conductor ya tiene un camión asignado');
                }
            }
            
            // Crear el camión
            $camion = $this->camionRepository->create($camionDTO->toArray());
            
            $this->logInfo('Camión creado', [
                'id' => $camion->id_camion, 
                'placa' => $camion->placa
            ]);
            
            return CamionDTO::fromModel($camion->fresh('conductor.user'))->toResponseArray();
        });
    }

    /**
     * Actualizar camión
     */
    public function updateCamion(int $id, CamionDTO $camionDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $camionDTO) {
            $camion = $this->camionRepository->find($id);
            
            if (!$camion) {
                throw new \Exception('Camión no encontrado');
            }
            
            // Validar placa única (excepto el mismo camión)
            $existente = $this->camionRepository->findByPlaca($camionDTO->placa);
            if ($existente && $existente->id_camion !== $id) {
                throw new \Exception('Ya existe otro camión con esta placa');
            }

            // Validar conductor si se proporciona y es diferente al actual
            if ($camionDTO->id_conductor && $camionDTO->id_conductor !== $camion->id_conductor) {
                $conductor = $this->conductorRepository->find($camionDTO->id_conductor);
                if (!$conductor) {
                    throw new \Exception('El conductor especificado no existe');
                }
                
                // Verificar si el conductor ya tiene otro camión asignado
                $camionesConductor = $this->camionRepository->getCamionesPorConductor($camionDTO->id_conductor);
                if ($camionesConductor->isNotEmpty() && $camionesConductor->first()->id_camion !== $id) {
                    throw new \Exception('El conductor ya tiene otro camión asignado');
                }
            }
            
            // Actualizar camión
            $camion = $this->camionRepository->update($camion, $camionDTO->toArray());
            
            $this->logInfo('Camión actualizado', ['id' => $camion->id_camion]);
            
            return CamionDTO::fromModel($camion->fresh('conductor.user'))->toResponseArray();
        });
    }

    /**
     * Eliminar camión
     */
    public function deleteCamion(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $camion = $this->camionRepository->find($id);
            
            if (!$camion) {
                throw new \Exception('Camión no encontrado');
            }
            
            // Verificar si tiene asignaciones pendientes
            if ($camion->asignacionesActivas()->exists()) {
                throw new \Exception('No se puede eliminar el camión porque tiene asignaciones activas');
            }
            
            $result = $this->camionRepository->delete($camion);
            
            $this->logInfo('Camión eliminado', ['id' => $id]);
            
            return $result;
        });
    }

    /**
     * Obtener camiones disponibles
     */
    public function getCamionesDisponibles(): array
    {
        $camiones = $this->camionRepository->findDisponibles();
        
        return $camiones->map(fn($camion) => [
            'value' => $camion->id_camion,
            'label' => $camion->placa . ' - ' . $camion->capacidad_toneladas . 't',
            'conductor' => $camion->conductor && $camion->conductor->user ? 
                $camion->conductor->user->nombres . ' ' . $camion->conductor->user->apellidos : 
                'Sin conductor'
        ])->toArray();
    }

    /**
     * Obtener camiones para selector
     */
    public function getCamionesForSelect(): array
    {
        $camiones = $this->camionRepository->getAllWithConductor();
        
        return $camiones->map(fn($camion) => [
            'value' => $camion->id_camion,
            'label' => $camion->placa,
            'capacidad' => $camion->capacidad_toneladas,
            'estado' => $camion->estado_vehiculo,
            'conductor' => $camion->conductor && $camion->conductor->user ? 
                $camion->conductor->user->nombres . ' ' . $camion->conductor->user->apellidos : 
                null
        ])->toArray();
    }

    /**
     * Obtener camiones disponibles para una fecha específica
     */
    public function getCamionesDisponiblesParaFecha(string $fecha): array
    {
        $camiones = $this->camionRepository->findDisponiblesParaFecha($fecha);

        return $camiones->map(fn($camion) => [
            'value' => $camion->id_camion,
            'label' => $camion->placa . ' - ' . $camion->capacidad_toneladas . 't',
            'conductor' => $camion->conductor && $camion->conductor->user ? 
                $camion->conductor->user->nombres . ' ' . $camion->conductor->user->apellidos : 
                'Sin conductor'
        ])->toArray();
    }

    /**
     * Cambiar estado del camión
     */
    public function cambiarEstado(int $id, string $estado): array
    {
        return $this->executeInTransaction(function () use ($id, $estado) {
            $camion = $this->camionRepository->find($id);
            
            if (!$camion) {
                throw new \Exception('Camión no encontrado');
            }
            
            $estadosValidos = ['operativo', 'mantenimiento', 'fuera_servicio'];
            if (!in_array($estado, $estadosValidos)) {
                throw new \Exception('Estado no válido');
            }
            
            // Si se va a poner en mantenimiento, verificar que no tenga asignaciones activas
            if ($estado === 'mantenimiento' && $camion->asignacionesActivas()->exists()) {
                throw new \Exception('No se puede poner en mantenimiento un camión con asignaciones activas');
            }
            
            $camion->estado_vehiculo = $estado;
            $camion->save();
            
            $this->logInfo('Estado de camión actualizado', [
                'id' => $id, 
                'nuevo_estado' => $estado
            ]);
            
            return CamionDTO::fromModel($camion->fresh('conductor.user'))->toResponseArray();
        });
    }

    /**
     * Asignar conductor a camión
     */
    public function asignarConductor(int $id, int $idConductor): array
    {
        return $this->executeInTransaction(function () use ($id, $idConductor) {
            $camion = $this->camionRepository->find($id);
            
            if (!$camion) {
                throw new \Exception('Camión no encontrado');
            }
            
            $conductor = $this->conductorRepository->find($idConductor);
            if (!$conductor) {
                throw new \Exception('Conductor no encontrado');
            }
            
            // Verificar si el conductor ya tiene un camión asignado
            $camionesConductor = $this->camionRepository->getCamionesPorConductor($idConductor);
            if ($camionesConductor->isNotEmpty() && $camionesConductor->first()->id_camion !== $id) {
                throw new \Exception('El conductor ya tiene otro camión asignado');
            }
            
            $camion->id_conductor = $idConductor;
            $camion->save();
            
            $this->logInfo('Conductor asignado a camión', [
                'camion_id' => $id,
                'conductor_id' => $idConductor
            ]);
            
            return CamionDTO::fromModel($camion->fresh('conductor.user'))->toResponseArray();
        });
    }

    /**
     * Quitar conductor de camión
     */
    public function quitarConductor(int $id): array
    {
        return $this->executeInTransaction(function () use ($id) {
            $camion = $this->camionRepository->find($id);
            
            if (!$camion) {
                throw new \Exception('Camión no encontrado');
            }
            
            $camion->id_conductor = null;
            $camion->save();
            
            $this->logInfo('Conductor removido de camión', ['camion_id' => $id]);
            
            return CamionDTO::fromModel($camion->fresh())->toResponseArray();
        });
    }

    /**
     * Obtener conductores disponibles para asignar
     */
    public function getConductoresDisponibles(): array
    {
        // Obtener IDs de conductores que ya tienen camión asignado
        $conductoresConCamion = $this->camionRepository->getModel()
            ->whereNotNull('id_conductor')
            ->pluck('id_conductor')
            ->toArray();
        
        // Obtener todos los conductores
        $conductores = $this->conductorRepository->all();
        
        return $conductores
            ->filter(function($conductor) use ($conductoresConCamion) {
                // Filtrar conductores que no tengan camión asignado
                return !in_array($conductor->id_conductor, $conductoresConCamion);
            })
            ->map(fn($conductor) => [
                'value' => $conductor->id_conductor,
                'label' => $conductor->user ? 
                    $conductor->user->nombres . ' ' . $conductor->user->apellidos . ' - ' . $conductor->licencia : 
                    'Conductor #' . $conductor->id_conductor,
                'licencia' => $conductor->licencia,
                'categoria' => $conductor->categoria_licencia,
                'disponible' => $conductor->disponible
            ])
            ->values()
            ->toArray();
    }
}