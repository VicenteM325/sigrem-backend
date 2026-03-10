<?php

namespace App\DTOs\CamionesDTOs;

class CamionDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $id_conductor,
        public readonly string $placa,
        public readonly float $capacidad_toneladas,
        public readonly string $estado_vehiculo,
        public readonly ?array $conductor = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_conductor: $data['id_conductor'] ?? null,
            placa: $data['placa'],
            capacidad_toneladas: (float) $data['capacidad_toneladas'],
            estado_vehiculo: $data['estado_vehiculo'] ?? 'operativo',
            conductor: $data['conductor'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_conductor: $data['id_conductor'] ?? null,
            placa: $data['placa'],
            capacidad_toneladas: (float) $data['capacidad_toneladas'],
            estado_vehiculo: $data['estado_vehiculo'] ?? 'operativo',
            conductor: $data['conductor'] ?? []
        );
    }

    public static function fromModel($model): self
    {
        $conductorData = [];
        if ($model->conductor && $model->conductor->user) {
            $conductorData = [
                'id' => $model->conductor->id_conductor,
                'nombre_completo' => $model->conductor->user->nombres . ' ' . $model->conductor->user->apellidos,
                'licencia' => $model->conductor->licencia,
                'categoria' => $model->conductor->categoria_licencia,
                'disponible' => $model->conductor->disponible,
                'email' => $model->conductor->user->email,
                'telefono' => $model->conductor->user->telefono
            ];
        }

        return new self(
            id: $model->id_camion,
            id_conductor: $model->id_conductor,
            placa: $model->placa,
            capacidad_toneladas: (float) $model->capacidad_toneladas,
            estado_vehiculo: $model->estado_vehiculo,
            conductor: $conductorData
        );
    }

    public function toArray(): array
    {
        return [
            'id_conductor' => $this->id_conductor,
            'placa' => $this->placa,
            'capacidad_toneladas' => $this->capacidad_toneladas,
            'estado_vehiculo' => $this->estado_vehiculo
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'capacidad' => $this->capacidad_toneladas,
            'estado' => $this->estado_vehiculo,
            'conductor' => $this->conductor
        ];
    }
}