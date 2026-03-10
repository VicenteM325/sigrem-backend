<?php

namespace App\Services\RutasService;

use App\DTOs\RutasDTOs\AsignacionRutaCamionDTO;
use App\Repositories\AsignacionRutaCamionRepository;
use App\Repositories\RutaRepository;
use App\Repositories\CamionRepository;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsignacionRutaCamionService extends BaseService
{
    public function __construct(
        private AsignacionRutaCamionRepository $asignacionRepository,
        private RutaRepository $rutaRepository,
        private CamionRepository $camionRepository
    ) {}

    /**
     * Obtener todas las asignaciones
     */
    public function getAllAsignaciones(array $filtros = []): array
    {
        $asignaciones = $this->asignacionRepository->getAllWithRelations($filtros);

        return [
            'asignaciones' => $asignaciones->map(fn($asignacion) => AsignacionRutaCamionDTO::fromModel($asignacion)->toResponseArray()),
            'total' => $asignaciones->count(),
            'filtros_aplicados' => $filtros
        ];
    }

    /**
     * Obtener asignación por ID
     */
    public function getAsignacionById(int $id): ?array
    {
        $asignacion = $this->asignacionRepository->findWithRelations($id);
        
        if (!$asignacion) {
            return null;
        }
        
        return AsignacionRutaCamionDTO::fromModel($asignacion)->toResponseArray();
    }

   /**
    * Crear nueva asignación
    */
    public function createAsignacion(AsignacionRutaCamionDTO $asignacionDTO): array
    {
        return $this->executeInTransaction(function () use ($asignacionDTO) {
        // Validar que la ruta existe
        $ruta = $this->rutaRepository->find($asignacionDTO->id_ruta);
        if (!$ruta) {
            throw new \Exception('La ruta especificada no existe');
        }

        // Validar que el camión existe
        $camion = $this->camionRepository->find($asignacionDTO->id_camion);
        if (!$camion) {
            throw new \Exception('El camión especificado no existe');
        }

        // Validar que el camión esté operativo
        if ($camion->estado_vehiculo !== 'operativo') {
            throw new \Exception('El camión no está operativo para ser asignado');
        }

        // Validar que no haya asignación para la misma ruta en la misma fecha
        $asignacionExistente = $this->asignacionRepository->findAsignacionActivaByRuta(
            $asignacionDTO->id_ruta, 
            $asignacionDTO->fecha_programada
        );
        
        if ($asignacionExistente) {
            throw new \Exception('La ruta ya tiene una asignación para esta fecha');
        }

        // Validar que el camión no tenga otra asignación en la misma fecha
        $countAsignacionesCamion = $this->asignacionRepository->countAsignacionesByCamionAndFecha(
            $asignacionDTO->id_camion,
            $asignacionDTO->fecha_programada
        );
        
        if ($countAsignacionesCamion > 0) {
            throw new \Exception('El camión ya tiene una asignación para esta fecha');
        }

        // Calcular total estimado si no viene
        if (!$asignacionDTO->total_estimado_kg) {
            $asignacionDTO->total_estimado_kg = $ruta->distancia_km * 100;
        }

        try {
            // Crear la asignación
            $asignacion = $this->asignacionRepository->create($asignacionDTO->toArray());
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { 
                throw new \Exception('Ya existe una asignación para esta ruta en la fecha seleccionada');
            }
            throw $e;
        }
        
        $this->logInfo('Asignación creada', [
            'id' => $asignacion->id_asignacion,
            'ruta' => $asignacionDTO->id_ruta,
            'camion' => $asignacionDTO->id_camion,
            'fecha' => $asignacionDTO->fecha_programada
        ]);
        
        return AsignacionRutaCamionDTO::fromModel($asignacion->fresh(['ruta.zona', 'camion.conductor.user']))->toResponseArray();
    });
}

    /**
    * Actualizar asignación
    */
    public function updateAsignacion(int $id, AsignacionRutaCamionDTO $asignacionDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $asignacionDTO) {
            $asignacion = $this->asignacionRepository->find($id);
        
            if (!$asignacion) {
                throw new \Exception('Asignación no encontrada');
            }

            // No permitir actualizar asignaciones completadas o canceladas
            if (in_array($asignacion->estado, ['completada', 'cancelada'])) {
                throw new \Exception('No se puede actualizar una asignación completada o cancelada');
            }

            // Preparar datos para actualizar
            $datosActualizar = $asignacionDTO->toArray();
        
            // Si se está cambiando la ruta o la fecha, validar unique constraint
            if ((isset($datosActualizar['id_ruta']) && $datosActualizar['id_ruta'] != $asignacion->id_ruta) ||
                (isset($datosActualizar['fecha_programada']) && $datosActualizar['fecha_programada'] != $asignacion->fecha_programada->format('Y-m-d'))) {
            
                $nuevaRuta = $datosActualizar['id_ruta'] ?? $asignacion->id_ruta;
                $nuevaFecha = $datosActualizar['fecha_programada'] ?? $asignacion->fecha_programada->format('Y-m-d');
            
                $asignacionExistente = $this->asignacionRepository->findAsignacionActivaByRuta($nuevaRuta, $nuevaFecha);
                if ($asignacionExistente && $asignacionExistente->id_asignacion !== $id) {
                    throw new \Exception('Ya existe otra asignación para esta ruta en la fecha seleccionada');
                }
            }

            // Si se está cambiando el camión, validar disponibilidad
            if (isset($datosActualizar['id_camion']) && $datosActualizar['id_camion'] != $asignacion->id_camion) {
                $camion = $this->camionRepository->find($datosActualizar['id_camion']);
                if (!$camion) {
                    throw new \Exception('El camión especificado no existe');
                }
            
                if ($camion->estado_vehiculo !== 'operativo') {
                    throw new \Exception('El camión no está operativo para ser asignado');
                }

                $fechaAsignacion = $datosActualizar['fecha_programada'] ?? $asignacion->fecha_programada->format('Y-m-d');
                $countAsignacionesCamion = $this->asignacionRepository->countAsignacionesByCamionAndFecha(
                    $datosActualizar['id_camion'],
                    $fechaAsignacion
                );
            
                if ($countAsignacionesCamion > 0) {
                    throw new \Exception('El nuevo camión ya tiene una asignación para esta fecha');
                }
            }

            try {
                // Actualizar asignación
                $asignacion = $this->asignacionRepository->update($asignacion, $datosActualizar);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] == 1062) {
                    throw new \Exception('Ya existe otra asignación para esta ruta en la fecha seleccionada');
                }
                throw $e;
            }
        
            $this->logInfo('Asignación actualizada', ['id' => $id]);
        
            return AsignacionRutaCamionDTO::fromModel($asignacion->fresh(['ruta.zona', 'camion.conductor.user']))->toResponseArray();
        });
    }

    /**
     * Cambiar estado de la asignación
     */
    public function cambiarEstado(int $id, string $estado, ?array $datosAdicionales = null): array
    {
        return $this->executeInTransaction(function () use ($id, $estado, $datosAdicionales) {
            $asignacion = $this->asignacionRepository->find($id);
            
            if (!$asignacion) {
                throw new \Exception('Asignación no encontrada');
            }

            $estadosValidos = ['programada', 'en_proceso', 'completada', 'cancelada'];
            if (!in_array($estado, $estadosValidos)) {
                throw new \Exception('Estado no válido');
            }

            // Validar transiciones de estado
            $transicionesPermitidas = [
                'programada' => ['en_proceso', 'cancelada'],
                'en_proceso' => ['completada', 'cancelada'],
                'completada' => [],
                'cancelada' => ['programada']
            ];

            if (!in_array($estado, $transicionesPermitidas[$asignacion->estado] ?? [])) {
                throw new \Exception("No se puede cambiar de estado '{$asignacion->estado}' a '{$estado}'");
            }

            // Si se va a iniciar la ruta, validar que el camión siga operativo
            if ($estado === 'en_proceso') {
                $camion = $this->camionRepository->find($asignacion->id_camion);
                if ($camion->estado_vehiculo !== 'operativo') {
                    throw new \Exception('No se puede iniciar la ruta porque el camión no está operativo');
                }
            }

            // Actualizar estado
            $asignacion->estado = $estado;
            
            if ($datosAdicionales && $estado === 'completada') {
                if (isset($datosAdicionales['total_real_kg'])) {
                    $asignacion->total_estimado_kg = $datosAdicionales['total_real_kg'];
                }
            }
            
            $asignacion->save();

            $this->logInfo('Estado de asignación actualizado', [
                'id' => $id,
                'estado_anterior' => $asignacion->getOriginal('estado'),
                'nuevo_estado' => $estado
            ]);

            return AsignacionRutaCamionDTO::fromModel($asignacion->fresh(['ruta.zona', 'camion.conductor.user']))->toResponseArray();
        });
    }

    /**
     * Eliminar asignación
     */
    public function deleteAsignacion(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $asignacion = $this->asignacionRepository->find($id);
            
            if (!$asignacion) {
                throw new \Exception('Asignación no encontrada');
            }

            // No permitir eliminar asignaciones en proceso o completadas
            if (in_array($asignacion->estado, ['en_proceso', 'completada'])) {
                throw new \Exception('No se puede eliminar una asignación en proceso o completada');
            }

            $result = $this->asignacionRepository->delete($asignacion);
            
            $this->logInfo('Asignación eliminada', ['id' => $id]);
            
            return $result;
        });
    }

    /**
     * Obtener asignaciones pendientes
     */
    public function getAsignacionesPendientes(): array
    {
        $asignaciones = $this->asignacionRepository->getAsignacionesPendientes();
        
        return [
            'asignaciones' => $asignaciones->map(fn($asignacion) => AsignacionRutaCamionDTO::fromModel($asignacion)->toResponseArray()),
            'total' => $asignaciones->count(),
            'fecha' => Carbon::today()->format('Y-m-d')
        ];
    }

    /**
     * Obtener asignaciones por fecha
     */
    public function getAsignacionesByFecha(string $fecha): array
    {
        $asignaciones = $this->asignacionRepository->getAsignacionesByFecha($fecha);
        
        return [
            'asignaciones' => $asignaciones->map(fn($asignacion) => AsignacionRutaCamionDTO::fromModel($asignacion)->toResponseArray()),
            'fecha' => $fecha,
            'total' => $asignaciones->count()
        ];
    }

    /**
     * Obtener calendario de asignaciones
     */
    public function getCalendario(string $fechaInicio, string $fechaFin): array
    {
        $asignaciones = $this->asignacionRepository->getAsignacionesByRangoFechas($fechaInicio, $fechaFin);
        
        // Agrupar por fecha para calendario
        $calendario = [];
        foreach ($asignaciones as $asignacion) {
            $fecha = $asignacion->fecha_programada->format('Y-m-d');
            if (!isset($calendario[$fecha])) {
                $calendario[$fecha] = [
                    'fecha' => $fecha,
                    'asignaciones' => [],
                    'total' => 0
                ];
            }
            
            $calendario[$fecha]['asignaciones'][] = AsignacionRutaCamionDTO::fromModel($asignacion)->toResponseArray();
            $calendario[$fecha]['total']++;
        }

        return [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'calendario' => array_values($calendario),
            'total_asignaciones' => $asignaciones->count(),
            'total_dias' => count($calendario)
        ];
    }

    /**
     * Obtener estadísticas de asignaciones
     */
    public function getEstadisticas(string $fechaInicio, string $fechaFin): array
    {
        return $this->asignacionRepository->getEstadisticasPorRangoFechas($fechaInicio, $fechaFin);
    }

    /**
     * Verificar disponibilidad de camión para una fecha
     */
    public function verificarDisponibilidadCamion(int $idCamion, string $fecha): array
    {
        $camion = $this->camionRepository->find($idCamion);
        
        if (!$camion) {
            throw new \Exception('Camión no encontrado');
        }

        $countAsignaciones = $this->asignacionRepository->countAsignacionesByCamionAndFecha($idCamion, $fecha);
        $asignacionActiva = $this->asignacionRepository->findAsignacionActivaByCamion($idCamion);

        return [
            'id_camion' => $idCamion,
            'placa' => $camion->placa,
            'fecha_consulta' => $fecha,
            'disponible' => $countAsignaciones === 0 && $camion->estado_vehiculo === 'operativo',
            'estado_camion' => $camion->estado_vehiculo,
            'asignaciones_existentes' => $countAsignaciones,
            'tiene_asignacion_activa' => $asignacionActiva ? true : false,
            'asignacion_activa' => $asignacionActiva ? [
                'id' => $asignacionActiva->id_asignacion,
                'fecha' => $asignacionActiva->fecha_programada->format('Y-m-d'),
                'ruta' => $asignacionActiva->ruta->nombre_ruta ?? null
            ] : null
        ];
    }
}