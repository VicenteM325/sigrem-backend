<?php

namespace App\DTOs\RutasDTOs;

class RutaMapaDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly array $geojson,
        public readonly string $horario,
        public readonly array $dias,
        public readonly string $color
    ) {}

    public static function fromModel($ruta): self
    {
        return new self(
            id: $ruta->id_ruta,
            nombre: $ruta->nombre_ruta,
            geojson: $ruta->geojson,
            horario: $ruta->horario_inicio->format('H:i') . ' - ' . $ruta->horario_fin->format('H:i'),
            dias: $ruta->diasRecoleccion->pluck('nombre_dia')->toArray(),
            color: $ruta->getColorByEstado()
        );
    }

    public function toFeatureCollection(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                $this->geojson
            ]
        ];
    }
}