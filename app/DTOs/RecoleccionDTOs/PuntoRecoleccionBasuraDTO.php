<?php

namespace App\DTOs\RecoleccionDTOs;

class PuntoRecoleccionBasuraDTO
{
    public function __construct(
        public readonly ?int $id_punto_basura,
        public readonly int $id_recoleccion,
        public readonly float $latitud,
        public readonly float $longitud,
        public readonly float $volumen_estimado_kg,
        public readonly string $estado_recoleccion
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id_punto_basura: $model->id_punto_basura,
            id_recoleccion: $model->id_recoleccion,
            latitud: (float) $model->latitud,
            longitud: (float) $model->longitud,
            volumen_estimado_kg: (float) $model->volumen_estimado_kg,
            estado_recoleccion: $model->estado_recoleccion
        );
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id_punto_basura,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'volumen_estimado' => $this->volumen_estimado_kg,
            'estado' => $this->estado_recoleccion
        ];
    }
}
