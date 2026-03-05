<?php
namespace App\DTOs\ResiduosDTOs;

class TipoResiduoDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombre: $data['nombre'],
            descripcion: $data['descripcion'] ?? null
        );
    }

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id_tipo_residuo,
            nombre: $model->nombre,
            descripcion: $model->descripcion
        );
    }

    public function toArray(): array
    {
        return [
            'id_tipo_residuo' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }
}