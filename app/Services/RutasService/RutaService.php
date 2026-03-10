<?php

namespace App\Services\RutasService;

use App\DTOs\RutasDTOs\RutaDTO;
use App\DTOs\RutasDTOs\RutaMapaDTO;
use App\Repositories\RutaRepository;
use App\Repositories\ZonaRepository;
use App\Repositories\EstadoRutaRepository;
use App\Models\rutas\Ruta;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class RutaService extends BaseService
{
    public function __construct(
        private RutaRepository $rutaRepository,
        private ZonaRepository $zonaRepository,
        private EstadoRutaRepository $estadoRutaRepository
    ) {}

    /**
     * Obtener todas las rutas
     */
    public function getAllRutas(): array
    {
        $rutas = $this->rutaRepository->getAllWithRelations();
        
        return [
            'rutas' => $rutas->map(fn($ruta) => $this->formatRutaResponse($ruta)),
            'total' => $rutas->count(),
            'estadisticas' => $this->rutaRepository->getEstadisticas()
        ];
    }

    /**
     * Obtener ruta por ID
     */
    public function getRutaById(int $id): ?array
    {
        $ruta = $this->rutaRepository->findRutaCompleta($id);
        
        if (!$ruta) {
            return null;
        }
        
        return $this->formatRutaCompletaResponse($ruta);
    }

    /**
     * Crear nueva ruta
     */
    public function createRuta(RutaDTO $rutaDTO): array
    {
        return $this->executeInTransaction(function () use ($rutaDTO) {
            // Validar que la zona existe
            $zona = $this->zonaRepository->find($rutaDTO->id_zona);
            if (!$zona) {
                throw new \Exception('La zona seleccionada no existe');
            }

            // Crear la ruta
            $ruta = $this->rutaRepository->create($rutaDTO->toArray());

            // Guardar puntos intermedios
            if (!empty($rutaDTO->puntos_intermedios)) {
                $puntosArray = array_map(
                    fn($punto) => ['lat' => $punto->lat, 'lng' => $punto->lng],
                    $rutaDTO->puntos_intermedios
                );
                $this->rutaRepository->savePuntosRuta($ruta->id_ruta, $puntosArray);
            }

            // Guardar días de recolección
            if (!empty($rutaDTO->dias_recoleccion)) {
                $this->rutaRepository->saveDiasRecoleccion($ruta, $rutaDTO->dias_recoleccion);
            }

            // Guardar tipos de residuo
            if (!empty($rutaDTO->tipos_residuo)) {
                $this->rutaRepository->syncTiposResiduo($ruta, $rutaDTO->tipos_residuo);
            }

            $this->logInfo('Ruta creada', [
                'id' => $ruta->id_ruta,
                'nombre' => $ruta->nombre_ruta
            ]);

            return $this->formatRutaResponse($ruta->fresh([
                'estado', 'zona', 'puntosRuta', 'tiposResiduo', 'diasRecoleccion'
            ]));
        });
    }

    /**
     * Actualizar ruta
     */
    public function updateRuta(int $id, RutaDTO $rutaDTO): array
    {
        return $this->executeInTransaction(function () use ($id, $rutaDTO) {
            $ruta = $this->rutaRepository->find($id);
            
            if (!$ruta) {
                throw new \Exception('Ruta no encontrada');
            }

            // Actualizar datos básicos
            $ruta = $this->rutaRepository->update($ruta, $rutaDTO->toArray());

            // Actualizar puntos intermedios
            if (!empty($rutaDTO->puntos_intermedios)) {
                $puntosArray = array_map(
                    fn($punto) => ['lat' => $punto->lat, 'lng' => $punto->lng],
                    $rutaDTO->puntos_intermedios
                );
                $this->rutaRepository->savePuntosRuta($id, $puntosArray);
            }

            // Actualizar días de recolección
            if (!empty($rutaDTO->dias_recoleccion)) {
                $this->rutaRepository->saveDiasRecoleccion($ruta, $rutaDTO->dias_recoleccion);
            }

            // Actualizar tipos de residuo
            if (!empty($rutaDTO->tipos_residuo)) {
                $this->rutaRepository->syncTiposResiduo($ruta, $rutaDTO->tipos_residuo);
            }

            $this->logInfo('Ruta actualizada', ['id' => $id]);

            return $this->formatRutaResponse($ruta->fresh([
                'estado', 'zona', 'puntosRuta', 'tiposResiduo', 'diasRecoleccion'
            ]));
        });
    }

    /**
     * Eliminar ruta
     */
    public function deleteRuta(int $id): bool
    {
        return $this->executeInTransaction(function () use ($id) {
            $ruta = $this->rutaRepository->find($id);
            
            if (!$ruta) {
                throw new \Exception('Ruta no encontrada');
            }

            // Verificar si tiene asignaciones activas
            if ($ruta->asignaciones()->whereIn('estado', ['programada', 'en_proceso'])->exists()) {
                throw new \Exception('No se puede eliminar una ruta con asignaciones activas');
            }

            $result = $this->rutaRepository->delete($ruta);
            
            $this->logInfo('Ruta eliminada', ['id' => $id]);
            
            return $result;
        });
    }

    /**
     * Obtener rutas para el mapa
     */
    public function getRutasParaMapa(): array
    {
        $rutas = $this->rutaRepository->getRutasActivas();
        
        $features = [];
        foreach ($rutas as $ruta) {
            $features[] = RutaMapaDTO::fromModel($ruta)->geojson;
        }
        
        return [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }

    /**
     * Cambiar estado de una ruta
     */
    public function cambiarEstado(int $id, int $idEstado): array
    {
        return $this->executeInTransaction(function () use ($id, $idEstado) {
            $ruta = $this->rutaRepository->find($id);
            
            if (!$ruta) {
                throw new \Exception('Ruta no encontrada');
            }

            $estado = $this->estadoRutaRepository->find($idEstado);
            if (!$estado) {
                throw new \Exception('Estado no válido');
            }

            $ruta = $this->rutaRepository->update($ruta, ['id_estado_ruta' => $idEstado]);
            
            $this->logInfo('Estado de ruta cambiado', [
                'id' => $id,
                'nuevo_estado' => $estado->nombre
            ]);

            return $this->formatRutaResponse($ruta);
        });
    }

    /**
     * Buscar rutas por nombre
     */
    public function searchRutas(string $termino): array
    {
        $rutas = $this->rutaRepository->searchByNombre($termino);
        
        return $rutas->map(fn($ruta) => [
            'id' => $ruta->id_ruta,
            'nombre' => $ruta->nombre_ruta,
            'zona' => $ruta->zona?->nombre_zona,
            'estado' => $ruta->estado?->nombre
        ])->toArray();
    }

    /**
     * Obtener rutas por zona
     */
    public function getRutasByZona(int $zonaId): array
    {
        $rutas = $this->rutaRepository->getByZona($zonaId);
        
        return $rutas->map(fn($ruta) => $this->formatRutaListResponse($ruta))->toArray();
    }

    /**
     * Duplicar una ruta (para crear variantes)
     */
    public function duplicarRuta(int $id, string $nuevoNombre): array
    {
        return $this->executeInTransaction(function () use ($id, $nuevoNombre) {
            $rutaOriginal = $this->rutaRepository->findWithRelations($id);
            
            if (!$rutaOriginal) {
                throw new \Exception('Ruta no encontrada');
            }

            // Crear nueva ruta con los mismos datos
            $nuevaRuta = $this->rutaRepository->create([
                'nombre_ruta' => $nuevoNombre,
                'descripcion' => $rutaOriginal->descripcion . ' (copia)',
                'id_zona' => $rutaOriginal->id_zona,
                'id_estado_ruta' => $rutaOriginal->id_estado_ruta,
                'coordenada_inicio_lat' => $rutaOriginal->coordenada_inicio_lat,
                'coordenada_inicio_lng' => $rutaOriginal->coordenada_inicio_lng,
                'coordenada_fin_lat' => $rutaOriginal->coordenada_fin_lat,
                'coordenada_fin_lng' => $rutaOriginal->coordenada_fin_lng,
                'distancia_km' => $rutaOriginal->distancia_km,
                'horario_inicio' => $rutaOriginal->horario_inicio,
                'horario_fin' => $rutaOriginal->horario_fin,
            ]);

            // Duplicar puntos
            $puntosArray = $rutaOriginal->puntosRuta
                ->map(fn($p) => ['lat' => $p->latitud, 'lng' => $p->longitud])
                ->toArray();
            $this->rutaRepository->savePuntosRuta($nuevaRuta->id_ruta, $puntosArray);

            // Duplicar días
            $diasArray = $rutaOriginal->diasRecoleccion->pluck('nombre_dia')->toArray();
            $this->rutaRepository->saveDiasRecoleccion($nuevaRuta, $diasArray);

            // Duplicar tipos de residuo
            $tiposArray = $rutaOriginal->tiposResiduo->pluck('id_tipo_residuo')->toArray();
            $this->rutaRepository->syncTiposResiduo($nuevaRuta, $tiposArray);

            $this->logInfo('Ruta duplicada', [
                'original' => $id,
                'nueva' => $nuevaRuta->id_ruta
            ]);

            return $this->formatRutaResponse($nuevaRuta->fresh([
                'estado', 'zona', 'puntosRuta', 'tiposResiduo', 'diasRecoleccion'
            ]));
        });
    }

    /**
     * Formato básico para listados
     */
    private function formatRutaResponse($ruta): array
    {
        return [
            'id' => $ruta->id_ruta,
            'nombre' => $ruta->nombre_ruta,
            'descripcion' => $ruta->descripcion,
            'zona' => [
                'id' => $ruta->zona->id_zona ?? null,
                'nombre' => $ruta->zona->nombre_zona ?? 'Sin zona'
            ],
            'estado' => [
                'id' => $ruta->estado->id_estado_ruta ?? null,
                'nombre' => $ruta->estado->nombre ?? 'Desconocido'
            ],
            'coordenadas' => [
                'inicio' => [
                    'lat' => $ruta->coordenada_inicio_lat,
                    'lng' => $ruta->coordenada_inicio_lng
                ],
                'fin' => [
                    'lat' => $ruta->coordenada_fin_lat,
                    'lng' => $ruta->coordenada_fin_lng
                ]
            ],
            'distancia_km' => $ruta->distancia_km,
            'horario' => $ruta->horario,
            'dias_recoleccion' => $ruta->diasRecoleccion->pluck('nombre_dia'),
            'tipos_residuo' => $ruta->tiposResiduo->map(fn($t) => [
                'id' => $t->id_tipo_residuo,
                'nombre' => $t->nombre
            ]),
            'created_at' => $ruta->created_at?->toISOString(),
            'updated_at' => $ruta->updated_at?->toISOString()
        ];
    }

    /**
     * Formato completo para detalle
     */
    private function formatRutaCompletaResponse($ruta): array
    {
        $base = $this->formatRutaResponse($ruta);
        
        $base['puntos_intermedios'] = $ruta->puntosRuta->map(fn($p) => [
            'lat' => $p->latitud,
            'lng' => $p->longitud,
            'orden' => $p->orden
        ]);

        $base['geojson'] = $ruta->geojson;

        $asignaciones = $ruta->asignaciones ?? collect();

        $base['estadisticas'] = [
          'total_asignaciones' => $asignaciones->count(),
            'ultima_asignacion' => $asignaciones->sortByDesc('fecha_programada')
                                                ->first()?->fecha_programada?->format('Y-m-d'),
            'total_puntos' => $ruta->puntosRuta->count(),
        ];

        return $base;
    }

    /**
     * Formato para listados simples
     */
    private function formatRutaListResponse($ruta): array
    {
        return [
            'id' => $ruta->id_ruta,
            'nombre' => $ruta->nombre_ruta,
            'zona' => $ruta->zona?->nombre_zona,
            'distancia' => $ruta->distancia_km,
            'horario' => $ruta->horario,
            'estado' => $ruta->estado?->nombre
        ];
    }
}