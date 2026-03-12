<?php

namespace App\DTOs\RecoleccionDTOs;

class RecoleccionDTO
{
    public function __construct(
        public readonly ?int $id_recoleccion,
        public readonly int $id_asignacion,
        public readonly ?string $hora_inicio,
        public readonly ?string $hora_fin,
        public readonly ?float $basura_recolectada_ton,
        public readonly string $estado_recoleccion,
        public readonly ?string $observaciones,
        public readonly ?array $asignacion = [],
        public readonly ?array $puntos_basura = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_recoleccion: $data['id_recoleccion'] ?? null,
            id_asignacion: $data['id_asignacion'],
            hora_inicio: $data['hora_inicio'] ?? null,
            hora_fin: $data['hora_fin'] ?? null,
            basura_recolectada_ton: isset($data['basura_recolectada_ton']) ? (float) $data['basura_recolectada_ton'] : null,
            estado_recoleccion: $data['estado_recoleccion'] ?? 'programada',
            observaciones: $data['observaciones'] ?? null,
            asignacion: $data['asignacion'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id_recoleccion: $data['id_recoleccion'] ?? null,
            id_asignacion: $data['id_asignacion'],
            hora_inicio: $data['hora_inicio'] ?? null,
            hora_fin: $data['hora_fin'] ?? null,
            basura_recolectada_ton: isset($data['basura_recolectada_ton']) ? (float) $data['basura_recolectada_ton'] : null,
            estado_recoleccion: $data['estado_recoleccion'] ?? 'programada',
            observaciones: $data['observaciones'] ?? null
        );
    }

    public static function fromModel($model): self
    {
        $asignacionData = [];
        if ($model->asignacion) {
            $asignacionData = [
                'id_asignacion' => $model->asignacion->id_asignacion,
                'ruta' => $model->asignacion->ruta ? [
                    'id' => $model->asignacion->ruta->id_ruta,
                    'nombre' => $model->asignacion->ruta->nombre_ruta,
                    'descripcion' => $model->asignacion->ruta->descripcion
                ] : null,
                'camion' => $model->asignacion->camion ? [
                    'id' => $model->asignacion->camion->id_camion,
                    'placa' => $model->asignacion->camion->placa,
                    'capacidad' => $model->asignacion->camion->capacidad_toneladas
                ] : null,
                'fecha_programada' => $model->asignacion->fecha_programada,
                'estado' => $model->asignacion->estado
            ];
        }

        $puntosData = [];
        if ($model->puntosBasura) {
            foreach ($model->puntosBasura as $punto) {
                $puntosData[] = PuntoRecoleccionBasuraDTO::fromModel($punto)->toResponseArray();
            }
        }

        return new self(
            id_recoleccion: $model->id_recoleccion,
            id_asignacion: $model->id_asignacion,
            hora_inicio: $model->hora_inicio?->format('Y-m-d H:i:s'),
            hora_fin: $model->hora_fin?->format('Y-m-d H:i:s'),
            basura_recolectada_ton: $model->basura_recolectada_ton,
            estado_recoleccion: $model->estado_recoleccion,
            observaciones: $model->observaciones,
            asignacion: $asignacionData,
            puntos_basura: $puntosData
        );
    }

    public function toArray(): array
    {
        return [
            'id_asignacion' => $this->id_asignacion,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'basura_recolectada_ton' => $this->basura_recolectada_ton,
            'estado_recoleccion' => $this->estado_recoleccion,
            'observaciones' => $this->observaciones
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id_recoleccion' => $this->id_recoleccion,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'basura_recolectada' => $this->basura_recolectada_ton,
            'estado' => $this->estado_recoleccion,
            'observaciones' => $this->observaciones,
            'asignacion' => $this->asignacion,
            'puntos_basura' => $this->puntos_basura
        ];
    }
}
