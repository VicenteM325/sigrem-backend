<?php

namespace App\Services\ContenedoresService;

use App\DTOs\ContenedoresDTOs\ContenedorDTO;
use App\DTOs\ContenedoresDTOs\VaciadoContenedorDTO;
use App\Repositories\ContenedorRepository;
use App\Repositories\VaciadoContenedorRepository;
use App\Services\BaseService;
use Carbon\Carbon;

class ContenedorService extends BaseService
{
    public function __construct(
        private ContenedorRepository $contenedorRepository,
        private VaciadoContenedorRepository $vaciadoRepository
    ) {}

    /**
     * Obtener todos los contenedores
     */
    public function getAllContenedores(): array
    {
        $contenedores = $this->contenedorRepository->getAllWithRelations();

        return [
            'contenedores' => $contenedores->map(fn($c) => ContenedorDTO::fromModel($c)->toResponseArray()),
            'total' => $contenedores->count()
        ];
    }

    /**
     * Obtener contenedor por ID
     */
    public function getContenedorById(int $id): ?array
    {
        $contenedor = $this->contenedorRepository->findWithRelations($id);

        if (!$contenedor) {
            return null;
        }

        return ContenedorDTO::fromModel($contenedor)->toResponseArray();
    }

    /**
     * Crear nuevo contenedor
     */
    public function createContenedor(ContenedorDTO $contenedorDTO): array
    {
        return $this->executeInTransaction(function () use ($contenedorDTO) {
            // Validar que no exista contenedor con mismo material en el mismo punto verde
            $existentes = $this->contenedorRepository->getByPuntoVerde($contenedorDTO->id_punto_verde);

            foreach ($existentes as $existente) {
                if ($existente->id_material === $contenedorDTO->id_material) {
                    throw new \Exception('Ya existe un contenedor para este material en el punto verde seleccionado');
                }
            }

            $contenedor = $this->contenedorRepository->create($contenedorDTO->toArray());

            $this->logInfo('Contenedor creado', [
                'id' => $contenedor->id_contenedor,
                'punto_verde' => $contenedorDTO->id_punto_verde,
                'material' => $contenedorDTO->id_material
            ]);

            return ContenedorDTO::fromModel($contenedor->fresh(['puntoVerde', 'material']))->toResponseArray();
        });
    }

    /**
     * Actualizar contenedor
     */
    public function updateContenedor(int $id, ContenedorDTO $contenedorDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $contenedorDTO) {
            $contenedor = $this->contenedorRepository->find($id);

            if (!$contenedor) {
                throw new \Exception('Contenedor no encontrado');
            }

            $contenedor = $this->contenedorRepository->update($contenedor, $contenedorDTO->toArray());

            $this->logInfo('Contenedor actualizado', ['id' => $id]);

            return ContenedorDTO::fromModel($contenedor->fresh(['puntoVerde', 'material']))->toResponseArray();
        });
    }

    /**
     * Eliminar contenedor
     */
    public function deleteContenedor(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $contenedor = $this->contenedorRepository->find($id);

            if (!$contenedor) {
                throw new \Exception('Contenedor no encontrado');
            }

            // Verificar si tiene vaciados pendientes
            if ($contenedor->vaciadosPendientes()->exists()) {
                throw new \Exception('No se puede eliminar un contenedor con vaciados pendientes');
            }

            $result = $this->contenedorRepository->delete($contenedor);

            $this->logInfo('Contenedor eliminado', ['id' => $id]);

            return $result;
        });
    }

    /**
     * Programar vaciado de contenedor
     */
    public function programarVaciado(VaciadoContenedorDTO $vaciadoDTO): array
    {
        return $this->executeInTransaction(function () use ($vaciadoDTO) {
            $contenedor = $this->contenedorRepository->find($vaciadoDTO->id_contenedor);

            if (!$contenedor) {
                throw new \Exception('Contenedor no encontrado');
            }

            // Verificar si ya existe vaciado programado para la misma fecha
            $existentes = $this->vaciadoRepository->getByContenedor($vaciadoDTO->id_contenedor);
            foreach ($existentes as $existente) {
                if ($existente->fecha_programada->format('Y-m-d') === $vaciadoDTO->fecha_programada) {
                    throw new \Exception('Ya existe un vaciado programado para este contenedor en la misma fecha');
                }
            }

            $vaciado = $this->vaciadoRepository->create($vaciadoDTO->toArray());

            $this->logInfo('Vaciado programado', [
                'id' => $vaciado->id_vaciado,
                'contenedor' => $vaciadoDTO->id_contenedor,
                'fecha' => $vaciadoDTO->fecha_programada
            ]);

            return VaciadoContenedorDTO::fromModel($vaciado->fresh('contenedor'))->toResponseArray();
        });
    }

    /**
     * Realizar vaciado de contenedor
     */
    public function realizarVaciado(int $idVaciado): array
    {
        return $this->executeInTransaction(function () use ($idVaciado) {
            $vaciado = $this->vaciadoRepository->findWithRelations($idVaciado);

            if (!$vaciado) {
                throw new \Exception('Vaciado no encontrado');
            }

            if ($vaciado->estado !== 'programado') {
                throw new \Exception('Este vaciado ya fue realizado o cancelado');
            }

            if ($vaciado->fecha_programada->isFuture()) {
                throw new \Exception('No se puede realizar un vaciado antes de la fecha programada');
            }

            $vaciado->estado = 'realizado';
            $vaciado->fecha_real = Carbon::now();
            $vaciado->save();

            // Resetear el porcentaje de llenado del contenedor
            $this->contenedorRepository->actualizarLlenado($vaciado->id_contenedor, 0);

            $this->logInfo('Vaciado realizado', [
                'id' => $idVaciado,
                'contenedor' => $vaciado->id_contenedor
            ]);

            return VaciadoContenedorDTO::fromModel($vaciado->fresh('contenedor'))->toResponseArray();
        });
    }

    /**
     * Obtener contenedores por llenar (>=80%)
     */
    public function getContenedoresPorLlenar(): array
    {
        $contenedores = $this->contenedorRepository->getPorLlenar(80);

        return $contenedores->map(fn($c) => [
            'id' => $c->id_contenedor,
            'ubicacion' => $c->puntoVerde->nombre,
            'material' => $c->material->nombre_material,
            'capacidad' => $c->capacidad_m3,
            'llenado' => $c->porcentaje_llenado,
            'estado' => $c->estado_contenedor
        ])->toArray();
    }

    /**
     * Obtener vaciados pendientes
     */
    public function getVaciadosPendientes(): array
    {
        $vaciados = $this->vaciadoRepository->getPendientes();

        return $vaciados->map(fn($v) => VaciadoContenedorDTO::fromModel($v)->toResponseArray())->toArray();
    }
}
