<?php

namespace App\Services\RecoleccionService;

use App\DTOs\RecoleccionDTOs\RecoleccionDTO;
use App\Repositories\RecoleccionRepository;
use App\Repositories\AsignacionRutaCamionRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecoleccionService extends BaseService
{
    public function __construct(
        private RecoleccionRepository $recoleccionRepository,
        private AsignacionRutaCamionRepository $asignacionRepository
    ) {}

    /**
     * Obtener todas las recolecciones
     */
    public function getAllRecolecciones(): array
    {
        $user = Auth::user();

        $recolecciones = $this->recoleccionRepository->getAllWithAsignacion($user);

        return [
            'recolecciones' => $recolecciones
                ->map(fn($rec) => RecoleccionDTO::fromModel($rec)->toResponseArray()),
            'total' => $recolecciones->count()
        ];
    }

    /**
     * Obtener recolección por ID
     */
    public function getRecoleccionById(int $id): ?array
    {
        $user = Auth::user();

        $recoleccion = $this->recoleccionRepository->findWithAsignacion($id, $user);

        if (!$recoleccion) {
            return null;
        }

        return RecoleccionDTO::fromModel($recoleccion)->toResponseArray();
    }

    /**
     * Crear nueva recolección (se crea automáticamente al asignar ruta-camión)
     */
    public function createRecoleccion(RecoleccionDTO $recoleccionDTO): array
    {
        return $this->executeInTransaction(function () use ($recoleccionDTO) {
            // Verificar que la asignación existe
            $asignacion = $this->asignacionRepository->find($recoleccionDTO->id_asignacion);
            if (!$asignacion) {
                throw new \Exception('La asignación especificada no existe');
            }

            // Verificar que no exista ya una recolección para esta asignación
            $existente = $this->recoleccionRepository->findByAsignacion($recoleccionDTO->id_asignacion);
            if ($existente) {
                throw new \Exception('Ya existe una recolección para esta asignación');
            }

            // Crear la recolección
            $recoleccion = $this->recoleccionRepository->create($recoleccionDTO->toArray());

            $this->logInfo('Recolección creada', [
                'id' => $recoleccion->id_recoleccion,
                'id_asignacion' => $recoleccion->id_asignacion
            ]);

            return RecoleccionDTO::fromModel($recoleccion->fresh('asignacion'))->toResponseArray();
        });
    }

    /**
     * Iniciar recolección
     */
    public function iniciarRecoleccion(int $id): array
    {
        return $this->executeInTransaction(function () use ($id) {
            $recoleccion = $this->recoleccionRepository->find($id);

            if (!$recoleccion) {
                throw new \Exception('Recolección no encontrada');
            }

            if ($recoleccion->estado_recoleccion !== 'programada') {
                throw new \Exception('Solo se pueden iniciar recolecciones en estado programada');
            }

            // Verificar que la asignación esté en estado válido
            if (!in_array($recoleccion->asignacion->estado, ['programada', 'en_proceso'])) {
                throw new \Exception('La asignación no está en un estado válido para iniciar la recolección');
            }

            $recoleccion->hora_inicio = Carbon::now();
            $recoleccion->estado_recoleccion = 'en_proceso';
            $recoleccion->save();

            // Actualizar estado de la asignación si es necesario
            if ($recoleccion->asignacion->estado === 'programada') {
                $recoleccion->asignacion->estado = 'en_proceso';
                $recoleccion->asignacion->save();
            }

            $this->logInfo('Recolección iniciada', ['id' => $id]);

            return RecoleccionDTO::fromModel($recoleccion->fresh('asignacion'))->toResponseArray();
        });
    }

    /**
     * Finalizar recolección
     */
    public function finalizarRecoleccion(int $id, float $basuraRecolectada, ?string $observaciones = null): array
    {
        return $this->executeInTransaction(function () use ($id, $basuraRecolectada, $observaciones) {

            $user = Auth::user();
            $recoleccion = $this->recoleccionRepository->findWithAsignacion($id, $user);

            if (!$recoleccion) {
                throw new \Exception('Recolección no encontrada');
            }

            if ($recoleccion->estado_recoleccion !== 'en_proceso') {
                throw new \Exception('Solo se pueden finalizar recolecciones en progreso');
            }

            if (!$recoleccion->hora_inicio) {
                throw new \Exception('La recolección no tiene hora de inicio registrada');
            }
            //Basura de kg a toneladas
            $basuraRecolectadaTon = $basuraRecolectada / 1000;

            // Validar que la basura no exceda la capacidad del camión
            if ($basuraRecolectadaTon > $recoleccion->asignacion->camion->capacidad_toneladas) {
                throw new \Exception('La cantidad de basura excede la capacidad del camión');
            }

            $recoleccion->hora_fin = Carbon::now();
            $recoleccion->basura_recolectada_ton = $basuraRecolectadaTon;
            $recoleccion->estado_recoleccion = 'completada';
            $recoleccion->observaciones = $observaciones;
            $recoleccion->save();

            // Actualizar estado de la asignación
            $recoleccion->asignacion->estado = 'completada';
            $recoleccion->asignacion->save();

            $this->logInfo('Recolección finalizada', [
                'id' => $id,
                'basura' => $basuraRecolectada
            ]);

            return RecoleccionDTO::fromModel($recoleccion->fresh('asignacion'))->toResponseArray();
        });
    }

    /**
     * Reportar incidencia en recolección
     */
    public function reportarIncidencia(int $id, string $observaciones, string $estado = 'incompleta'): array
    {
        return $this->executeInTransaction(function () use ($id, $observaciones, $estado) {
            $recoleccion = $this->recoleccionRepository->find($id);

            if (!$recoleccion) {
                throw new \Exception('Recolección no encontrada');
            }

            if (!in_array($recoleccion->estado_recoleccion, ['programada', 'en_proceso'])) {
                throw new \Exception('No se puede reportar incidencia en este estado');
            }

            $recoleccion->estado_recoleccion = $estado;
            $recoleccion->observaciones = $observaciones;

            if ($recoleccion->estado_recoleccion === 'en_proceso' && !$recoleccion->hora_fin) {
                $recoleccion->hora_fin = Carbon::now();
            }

            $recoleccion->save();

            // Actualizar estado de la asignación si es necesario
            if ($estado === 'incompleta') {
                $recoleccion->asignacion->estado = 'incompleta';
                $recoleccion->asignacion->save();
            }

            $this->logInfo('Incidencia reportada en recolección', [
                'id' => $id,
                'observaciones' => $observaciones
            ]);

            return RecoleccionDTO::fromModel($recoleccion->fresh('asignacion'))->toResponseArray();
        });
    }

    /**
     * Obtener recolecciones pendientes
     */
    public function getRecoleccionesPendientes(): array
    {
        $user = Auth::user();

        $recolecciones = $this->recoleccionRepository->findPendientes($user);

        return $recolecciones->map(fn($rec) => [
            'id' => $rec->id_recoleccion,
            'ruta' => $rec->asignacion->ruta->nombre_ruta ?? 'N/A',
            'camion' => $rec->asignacion->camion->placa ?? 'N/A',
            'fecha_programada' => $rec->asignacion->fecha_programada,
            'estado' => $rec->estado_recoleccion
        ])->toArray();
    }

    /**
     * Obtener recolecciones en progreso
     */
    public function getRecoleccionesEnProceso(): array
    {
        $user = Auth::user();

        $recolecciones = $this->recoleccionRepository->findEnProceso($user);

        return $recolecciones->map(fn($rec) => [
            'id' => $rec->id_recoleccion,
            'ruta' => $rec->asignacion->ruta->nombre_ruta ?? 'N/A',
            'camion' => $rec->asignacion->camion->placa ?? 'N/A',
            'hora_inicio' => $rec->hora_inicio?->format('H:i:s'),
            'tiempo_transcurrido' => $rec->hora_inicio ?
                Carbon::parse($rec->hora_inicio)->diffInMinutes(Carbon::now()) . ' minutos' :
                'N/A'
        ])->toArray();
    }

    /**
     * Obtener estadísticas de recolección
     */
    public function getEstadisticas(string $fechaInicio, string $fechaFin): array
    {
        return $this->recoleccionRepository->getEstadisticasPorFecha($fechaInicio, $fechaFin);
    }

}
