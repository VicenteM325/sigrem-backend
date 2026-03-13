<?php

namespace App\Services\ReciclajeService;

use App\DTOs\ReciclajeDTOs\EntregaReciclajeDTO;
use App\Repositories\EntregaReciclajeRepository;
use App\Repositories\PuntoVerdeRepository;
use App\Repositories\TipoMaterialRepository;
use App\Repositories\CiudadanoRepository;
use App\Models\Ciudadano;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class EntregaReciclajeService extends BaseService
{
    public function __construct(
        private EntregaReciclajeRepository $entregaRepository,
        private PuntoVerdeRepository $puntoVerdeRepository,
        private TipoMaterialRepository $materialRepository,
    ) {}

    /**
     * Obtener todas las entregas
     */
    public function getAllEntregas(): array
    {
        $entregas = $this->entregaRepository->getAllWithRelations();

        return [
            'entregas' => $entregas->map(fn($e) => EntregaReciclajeDTO::fromModel($e)->toResponseArray()),
            'total' => $entregas->count()
        ];
    }

    /**
     * Obtener entrega por ID
     */
    public function getEntregaById(int $id): ?array
    {
        $entrega = $this->entregaRepository->findWithRelations($id);
        return $entrega ? EntregaReciclajeDTO::fromModel($entrega)->toResponseArray() : null;
    }

    /**
     * Crear nueva entrega
     */
    public function createEntrega(EntregaReciclajeDTO $entregaDTO): array
    {
        return DB::transaction(function () use ($entregaDTO) {
            // Validar punto verde
            $puntoVerde = $this->puntoVerdeRepository->find($entregaDTO->id_punto_verde);
            if (!$puntoVerde) {
                throw new \Exception('El punto verde especificado no existe');
            }

            // Validar material
            $material = $this->materialRepository->find($entregaDTO->id_material);
            if (!$material) {
                throw new \Exception('El tipo de material especificado no existe');
            }

            $ciudadano = Ciudadano::find($entregaDTO->id_ciudadano);

            // Validar cantidad
            if ($entregaDTO->cantidad_kg <= 0 || $entregaDTO->cantidad_kg > 100) {
                throw new \Exception('La cantidad debe ser entre 0.1 y 100 kg');
            }


            $entrega = $this->entregaRepository->create($entregaDTO->toArray());

            // Actualizar puntos del ciudadano
            $puntosGanados = floor($entregaDTO->cantidad_kg);
            $ciudadano->puntos_acumulados += $puntosGanados;
            $ciudadano->save();

            $this->logInfo('Entrega creada', [
                'id' => $entrega->id_entrega,
                'ciudadano' => $entregaDTO->id_ciudadano,
                'cantidad' => $entregaDTO->cantidad_kg
            ]);

            return EntregaReciclajeDTO::fromModel(
                $this->entregaRepository->findWithRelations($entrega->id_entrega)
            )->toResponseArray();
        });
    }

    /**
     * Obtener entregas por ciudadano
     */
    public function getEntregasByCiudadano(int $idCiudadano): array
    {
        $ciudadano = Ciudadano::with('user')->find($idCiudadano);
        if (!$ciudadano) {
            throw new \Exception('Ciudadano no encontrado');
        }

        $entregas = $this->entregaRepository->getByCiudadano($idCiudadano);

        return [
            'ciudadano' => [
                'id' => $ciudadano->id_ciudadano,
                'puntos' => $ciudadano->puntos_acumulados,
                'nivel' => $ciudadano->nivel
            ],
            'entregas' => $entregas->map(fn($e) => EntregaReciclajeDTO::fromModel($e)->toResponseArray()),
            'total' => $entregas->count(),
            'total_kg' => $entregas->sum('cantidad_kg')
        ];
    }

    /**
     * Obtener estadísticas
     */
    public function getEstadisticas(string $fechaInicio, string $fechaFin): array
    {
        return $this->entregaRepository->getEstadisticasPorPeriodo($fechaInicio, $fechaFin);
    }

    /**
     * Obtener entregas del día
     */
    public function getEntregasDelDia(): array
    {
        $fecha = now()->format('Y-m-d');
        $entregas = $this->entregaRepository->getByFecha($fecha);

        return [
            'fecha' => $fecha,
            'entregas' => $entregas->map(fn($e) => EntregaReciclajeDTO::fromModel($e)->toResponseArray()),
            'total' => $entregas->count(),
            'total_kg' => $entregas->sum('cantidad_kg')
        ];
    }
}
