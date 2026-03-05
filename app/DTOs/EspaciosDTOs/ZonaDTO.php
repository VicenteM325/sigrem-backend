<?php

namespace App\DTOs\EspaciosDTOs;

class ZonaDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre_zona,
        public readonly ?float $densidad_poblacional,
        public readonly ?array $coordenadas_poligono,
        public readonly ?array $tipos_zona = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombre_zona: $data['nombre_zona'],
            densidad_poblacional: isset($data['densidad_poblacional']) ? (float) $data['densidad_poblacional'] : null,
            coordenadas_poligono: $data['coordenadas_poligono'] ?? null,
            tipos_zona: $data['tipos_zona'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombre_zona: $data['nombre_zona'],
            densidad_poblacional: isset($data['densidad_poblacional']) ? (float) $data['densidad_poblacional'] : null,
            coordenadas_poligono: $data['coordenadas_poligono'] ?? null,
            tipos_zona: $data['tipos_zona'] ?? []
        );
    }

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id_zona,
            nombre_zona: $model->nombre_zona,
            densidad_poblacional: $model->densidad_poblacional,
            coordenadas_poligono: $model->coordenadas_poligono,
            tipos_zona: $model->tiposZona->pluck('nombre_tipo_zona')->toArray()
        );
    }

    public function toArray(): array
    {
        return [
            'id_zona' => $this->id,
            'nombre_zona' => $this->nombre_zona,
            'densidad_poblacional' => $this->densidad_poblacional,
            'coordenadas_poligono' => $this->coordenadas_poligono
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_zona,
            'densidad' => $this->densidad_poblacional,
            'tipos' => $this->tipos_zona,
            'poligono' => $this->coordenadas_poligono
        ];
    }
}