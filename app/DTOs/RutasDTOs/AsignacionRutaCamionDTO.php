<?php

namespace App\DTOs\RutasDTOs;

class AsignacionRutaCamionDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $id_ruta,
        public readonly int $id_camion,
        public readonly string $fecha_programada,
        public readonly string $estado,
        public readonly ?float $total_estimado_kg,
        public readonly ?array $ruta = [],
        public readonly ?array $camion = [],
        public readonly ?array $recoleccion = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_ruta: $data['id_ruta'],
            id_camion: $data['id_camion'],
            fecha_programada: $data['fecha_programada'],
            estado: $data['estado'] ?? 'programada',
            total_estimado_kg: isset($data['total_estimado_kg']) ? (float) $data['total_estimado_kg'] : null,
            ruta: $data['ruta'] ?? [],
            camion: $data['camion'] ?? [],
            recoleccion: $data['recoleccion'] ?? null
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_ruta: $data['id_ruta'],
            id_camion: $data['id_camion'],
            fecha_programada: $data['fecha_programada'],
            estado: $data['estado'] ?? 'programada',
            total_estimado_kg: isset($data['total_estimado_kg']) ? (float) $data['total_estimado_kg'] : null,
            ruta: $data['ruta'] ?? [],
            camion: $data['camion'] ?? [],
            recoleccion: $data['recoleccion'] ?? null
        );
    }

    public static function fromModel($model): self
    {
        $rutaData = [];
        if ($model->ruta) {
            $rutaData = [
                'id' => $model->ruta->id_ruta,
                'nombre' => $model->ruta->nombre_ruta,
                'distancia' => $model->ruta->distancia_km,
                'horario_inicio' => $model->ruta->horario_inicio_12h,
                'horario_fin' => $model->ruta->horario_fin_12h,
                'zona' => $model->ruta->zona ? [
                    'id' => $model->ruta->zona->id_zona,
                    'nombre' => $model->ruta->zona->nombre_zona
                ] : null
            ];
        }

        $camionData = [];
        if ($model->camion) {
            $conductorInfo = null;
            if ($model->camion->conductor && $model->camion->conductor->user) {
                $conductorInfo = [
                    'id' => $model->camion->conductor->id_conductor,
                    'nombre_completo' => $model->camion->conductor->user->nombres . ' ' . $model->camion->conductor->user->apellidos,
                    'licencia' => $model->camion->conductor->licencia,
                    'categoria' => $model->camion->conductor->categoria_licencia
                ];
            }

            $camionData = [
                'id' => $model->camion->id_camion,
                'placa' => $model->camion->placa,
                'capacidad' => $model->camion->capacidad_toneladas,
                'estado' => $model->camion->estado_vehiculo,
                'conductor' => $conductorInfo
            ];
        }
        $recoleccionData = null;
        if ($model->recoleccion) {
            $recoleccionData = [
                'id_recoleccion' => $model->recoleccion->id_recoleccion,
                'estado' => $model->recoleccion->estado_recoleccion,
                'hora_inicio' => $model->recoleccion->hora_inicio?->format('Y-m-d H:i:s'),
                'hora_fin' => $model->recoleccion->hora_fin?->format('Y-m-d H:i:s'),
                'basura_recolectada' => $model->recoleccion->basura_recolectada_ton,
                'observaciones' => $model->recoleccion->observaciones
            ];
        }

        return new self(
            id: $model->id_asignacion,
            id_ruta: $model->id_ruta,
            id_camion: $model->id_camion,
            fecha_programada: $model->fecha_programada,
            estado: $model->estado,
            total_estimado_kg: $model->total_estimado_kg ? (float) $model->total_estimado_kg : null,
            ruta: $rutaData,
            camion: $camionData,
            recoleccion: $recoleccionData
        );
    }

    public function toArray(): array
    {
        return [
            'id_ruta' => $this->id_ruta,
            'id_camion' => $this->id_camion,
            'fecha_programada' => $this->fecha_programada,
            'estado' => $this->estado,
            'total_estimado_kg' => $this->total_estimado_kg
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha_programada,
            'estado' => $this->estado,
            'total_estimado' => $this->total_estimado_kg,
            'ruta' => $this->ruta,
            'camion' => $this->camion,
            'recoleccion' => $this->recoleccion
        ];
    }
}
