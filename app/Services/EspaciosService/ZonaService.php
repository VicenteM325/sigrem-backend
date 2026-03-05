<?php

namespace App\Services\EspaciosService;

use App\DTOs\EspaciosDTOs\ZonaDTO;
use App\Repositories\ZonaRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BaseService;

class ZonaService extends BaseService
{
    public function __construct(
        private ZonaRepository $zonaRepository
    ) {}

    /**
     * Obtener todas las zonas
     */
    public function getAllZonas(): array
    {
        $zonas = $this->zonaRepository->all();
        
        return [
            'zonas' => $zonas->map(fn($zona) => ZonaDTO::fromModel($zona)->toResponseArray()),
            'total' => $zonas->count()
        ];
    }

    /**
     * Obtener zona por ID
     */
    public function getZonaById(int $id): ?array
    {
        $zona = $this->zonaRepository->findWithRelations($id);
        
        if (!$zona) {
            return null;
        }
        
        return ZonaDTO::fromModel($zona)->toResponseArray();
    }

    /**
     * Crear nueva zona
     */
    public function createZona(ZonaDTO $zonaDTO): array
    {
        return $this->executeInTransaction(function () use ($zonaDTO) {
            // Crear la zona
            $zona = $this->zonaRepository->create($zonaDTO->toArray());
            
            // Guardar tipos de zona si vienen
            if (!empty($zonaDTO->tipos_zona)) {
                foreach ($zonaDTO->tipos_zona as $tipo) {
                    $zona->tiposZona()->create(['nombre_tipo_zona' => $tipo]);
                }
            }
            
            $this->logInfo('Zona creada', ['id' => $zona->id_zona, 'nombre' => $zona->nombre_zona]);
            
            return ZonaDTO::fromModel($zona->fresh('tiposZona'))->toResponseArray();
        });
    }

    /**
     * Actualizar zona
     */
    public function updateZona(int $id, ZonaDTO $zonaDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $zonaDTO) {
            $zona = $this->zonaRepository->find($id);
            
            if (!$zona) {
                throw new \Exception('Zona no encontrada');
            }
            
            // Actualizar zona
            $zona = $this->zonaRepository->update($zona, $zonaDTO->toArray());
            
            // Actualizar tipos de zona
            if (!empty($zonaDTO->tipos_zona)) {
                $zona->tiposZona()->delete();
                foreach ($zonaDTO->tipos_zona as $tipo) {
                    $zona->tiposZona()->create(['nombre_tipo_zona' => $tipo]);
                }
            }
            
            $this->logInfo('Zona actualizada', ['id' => $zona->id_zona]);
            
            return ZonaDTO::fromModel($zona->fresh('tiposZona'))->toResponseArray();
        });
    }

    /**
     * Eliminar zona
     */
    public function deleteZona(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $zona = $this->zonaRepository->find($id);
            
            if (!$zona) {
                throw new \Exception('Zona no encontrada');
            }
            
            // Verificar si tiene rutas asociadas
            if ($zona->rutas()->exists()) {
                throw new \Exception('No se puede eliminar la zona porque tiene rutas asociadas');
            }
            
            $result = $this->zonaRepository->delete($zona);
            
            $this->logInfo('Zona eliminada', ['id' => $id]);
            
            return $result;
        });
    }

    /**
     * Obtener zonas para selector
     */
    public function getZonasForSelect(): array
    {
        $zonas = $this->zonaRepository->all();
        
        return $zonas->map(fn($zona) => [
            'value' => $zona->id_zona,
            'label' => $zona->nombre_zona,
            'densidad' => $zona->densidad_poblacional
        ])->toArray();
    }
}