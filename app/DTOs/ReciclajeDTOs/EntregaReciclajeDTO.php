<?php

namespace App\DTOs\ReciclajeDTOs;

class EntregaReciclajeDTO
{
    public function __construct(
        public readonly ?int $id_entrega,
        public readonly int $id_punto_verde,
        public readonly int $id_material,
        public readonly int $id_ciudadano,
        public readonly float $cantidad_kg,
        public readonly string $fecha_hora,
        public readonly ?array $punto_verde = [],
        public readonly ?array $material = [],
        public readonly ?array $ciudadano = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id_entrega: $data['id_entrega'] ?? null,
            id_punto_verde: $data['id_punto_verde'],
            id_material: $data['id_material'],
            id_ciudadano: $data['id_ciudadano'],
            cantidad_kg: (float) $data['cantidad_kg'],
            fecha_hora: now()->toDateTimeString()
        );
    }

    public static function fromModel($model): self
    {
        $puntoVerdeData = $model->puntoVerde ? [
            'id' => $model->puntoVerde->id_punto_verde,
            'nombre' => $model->puntoVerde->nombre,
            'direccion' => $model->puntoVerde->direccion
        ] : [];

        $materialData = $model->material ? [
            'id' => $model->material->id_material,
            'nombre' => $model->material->nombre_material
        ] : [];

        $ciudadanoData = $model->ciudadano && $model->ciudadano->user ? [
            'id' => $model->ciudadano->id_ciudadano,
            'nombre' => $model->ciudadano->user->nombres . ' ' . $model->ciudadano->user->apellidos,
            'puntos' => $model->ciudadano->puntos_acumulados,
            'nivel' => $model->ciudadano->nivel
        ] : [];

        return new self(
            id_entrega: $model->id_entrega,
            id_punto_verde: $model->id_punto_verde,
            id_material: $model->id_material,
            id_ciudadano: $model->id_ciudadano,
            cantidad_kg: (float) $model->cantidad_kg,
            fecha_hora: $model->fecha_hora->format('Y-m-d H:i:s'),
            punto_verde: $puntoVerdeData,
            material: $materialData,
            ciudadano: $ciudadanoData
        );
    }

    public function toArray(): array
    {
        return [
            'id_punto_verde' => $this->id_punto_verde,
            'id_material' => $this->id_material,
            'id_ciudadano' => $this->id_ciudadano,
            'cantidad_kg' => $this->cantidad_kg,
            'fecha_hora' => $this->fecha_hora
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id_entrega,
            'punto_verde' => $this->punto_verde,
            'material' => $this->material,
            'ciudadano' => $this->ciudadano,
            'cantidad' => $this->cantidad_kg,
            'fecha' => $this->fecha_hora
        ];
    }
}
