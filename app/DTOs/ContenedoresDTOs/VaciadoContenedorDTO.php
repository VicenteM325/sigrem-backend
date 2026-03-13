<?php

namespace App\DTOs\ContenedoresDTOs;

class VaciadoContenedorDTO
{
    public function __construct(
        public readonly ?int $id_vaciado,
        public readonly int $id_contenedor,
        public readonly string $fecha_programada,
        public readonly ?string $fecha_real,
        public readonly string $estado,
        public readonly ?array $contenedor = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_vaciado: $data['id_vaciado'] ?? null,
            id_contenedor: $data['id_contenedor'],
            fecha_programada: $data['fecha_programada'],
            fecha_real: $data['fecha_real'] ?? null,
            estado: $data['estado'] ?? 'programado',
            contenedor: $data['contenedor'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id_vaciado: $data['id_vaciado'] ?? null,
            id_contenedor: $data['id_contenedor'],
            fecha_programada: $data['fecha_programada'],
            fecha_real: $data['fecha_real'] ?? null,
            estado: $data['estado'] ?? 'programado'
        );
    }

    public static function fromModel($model): self
    {
        $contenedorData = [];
        if ($model->contenedor) {
            $contenedorData = [
                'id' => $model->contenedor->id_contenedor,
                'punto_verde' => $model->contenedor->puntoVerde->nombre ?? null,
                'material' => $model->contenedor->material->nombre_material ?? null,
                'capacidad' => $model->contenedor->capacidad_m3,
                'llenado' => $model->contenedor->porcentaje_llenado
            ];
        }

        return new self(
            id_vaciado: $model->id_vaciado,
            id_contenedor: $model->id_contenedor,
            fecha_programada: $model->fecha_programada->format('Y-m-d'),
            fecha_real: $model->fecha_real?->format('Y-m-d H:i:s'),
            estado: $model->estado,
            contenedor: $contenedorData
        );
    }

    public function toArray(): array
    {
        return [
            'id_contenedor' => $this->id_contenedor,
            'fecha_programada' => $this->fecha_programada,
            'fecha_real' => $this->fecha_real,
            'estado' => $this->estado
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id_vaciado,
            'fecha_programada' => $this->fecha_programada,
            'fecha_real' => $this->fecha_real,
            'estado' => $this->estado,
            'contenedor' => $this->contenedor
        ];
    }
}
