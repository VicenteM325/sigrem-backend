<?php

namespace App\DTOs\ContenedoresDTOs;

class ContenedorDTO
{
    public function __construct(
        public readonly ?int $id_contenedor,
        public readonly int $id_punto_verde,
        public readonly int $id_material,
        public readonly float $capacidad_m3,
        public readonly float $porcentaje_llenado,
        public readonly string $estado_contenedor,
        public readonly ?array $punto_verde = [],
        public readonly ?array $material = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_contenedor: $data['id_contenedor'] ?? null,
            id_punto_verde: $data['id_punto_verde'],
            id_material: $data['id_material'],
            capacidad_m3: (float) $data['capacidad_m3'],
            porcentaje_llenado: (float) ($data['porcentaje_llenado'] ?? 0),
            estado_contenedor: $data['estado_contenedor'] ?? 'disponible',
            punto_verde: $data['punto_verde'] ?? [],
            material: $data['material'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id_contenedor: $data['id_contenedor'] ?? null,
            id_punto_verde: $data['id_punto_verde'],
            id_material: $data['id_material'],
            capacidad_m3: (float) $data['capacidad_m3'],
            porcentaje_llenado: (float) ($data['porcentaje_llenado'] ?? 0),
            estado_contenedor: $data['estado_contenedor'] ?? 'disponible'
        );
    }

    public static function fromModel($model): self
    {
        $puntoVerdeData = [];
        if ($model->puntoVerde) {
            $puntoVerdeData = [
                'id' => $model->puntoVerde->id_punto_verde,
                'nombre' => $model->puntoVerde->nombre,
                'direccion' => $model->puntoVerde->direccion
            ];
        }

        $materialData = [];
        if ($model->material) {
            $materialData = [
                'id' => $model->material->id_material,
                'nombre' => $model->material->nombre_material,
                'descripcion' => $model->material->descripcion
            ];
        }

        return new self(
            id_contenedor: $model->id_contenedor,
            id_punto_verde: $model->id_punto_verde,
            id_material: $model->id_material,
            capacidad_m3: (float) $model->capacidad_m3,
            porcentaje_llenado: (float) $model->porcentaje_llenado,
            estado_contenedor: $model->estado_contenedor,
            punto_verde: $puntoVerdeData,
            material: $materialData
        );
    }

    public function toArray(): array
    {
        return [
            'id_punto_verde' => $this->id_punto_verde,
            'id_material' => $this->id_material,
            'capacidad_m3' => $this->capacidad_m3,
            'porcentaje_llenado' => $this->porcentaje_llenado,
            'estado_contenedor' => $this->estado_contenedor
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id_contenedor,
            'punto_verde' => $this->punto_verde,
            'material' => $this->material,
            'capacidad' => $this->capacidad_m3,
            'llenado' => $this->porcentaje_llenado,
            'estado' => $this->estado_contenedor
        ];
    }
}
