<?php

namespace App\DTOs\EspaciosDTOs;

class PuntoVerdeDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $id_zona,
        public readonly string $nombre,
        public readonly string $direccion,
        public readonly float $latitud,
        public readonly float $longitud,
        public readonly float $capacidad_total_m3,
        public readonly string $horario_atencion,
        public readonly ?int $id_encargado,
        public readonly ?array $zona = [],
        public readonly ?array $encargado = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_zona: (int) $data['id_zona'],
            nombre: $data['nombre'],
            direccion: $data['direccion'],
            latitud: (float) $data['latitud'],
            longitud: (float) $data['longitud'],
            capacidad_total_m3: (float) $data['capacidad_total_m3'],
            horario_atencion: $data['horario_atencion'],
            id_encargado: isset($data['id_encargado']) ? (int) $data['id_encargado'] : null,
            zona: $data['zona'] ?? [],
            encargado: $data['encargado'] ?? []
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_zona: (int) $data['id_zona'],
            nombre: $data['nombre'],
            direccion: $data['direccion'],
            latitud: (float) $data['latitud'],
            longitud: (float) $data['longitud'],
            capacidad_total_m3: (float) $data['capacidad_total_m3'],
            horario_atencion: $data['horario_atencion'],
            id_encargado: isset($data['id_encargado']) ? (int) $data['id_encargado'] : null
        );
    }

    public static function fromModel($model): self
    {
        $zonaData = [];
        if ($model->zona) {
            $zonaData = [
                'id' => $model->zona->id_zona,
                'nombre' => $model->zona->nombre_zona
            ];
        }

        $encargadoData = [];
        if ($model->encargado) {
            $encargadoData = [
                'id' => $model->encargado->id,
                'nombre' => $model->encargado->nombres . ' ' . $model->encargado->apellidos,
                'email' => $model->encargado->email,
                'telefono' => $model->encargado->telefono
            ];
        }

        return new self(
            id: $model->id_punto_verde,
            id_zona: $model->id_zona,
            nombre: $model->nombre,
            direccion: $model->direccion,
            latitud: (float) $model->latitud,
            longitud: (float) $model->longitud,
            capacidad_total_m3: (float) $model->capacidad_total_m3,
            horario_atencion: $model->horario_atencion,
            id_encargado: $model->id_encargado,
            zona: $zonaData,
            encargado: $encargadoData
        );
    }

    public function toArray(): array
    {
        return [
            'id_zona' => $this->id_zona,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'capacidad_total_m3' => $this->capacidad_total_m3,
            'horario_atencion' => $this->horario_atencion,
            'id_encargado' => $this->id_encargado
        ];
    }

    public function toResponseArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'ubicacion' => [
                'lat' => $this->latitud,
                'lng' => $this->longitud
            ],
            'capacidad' => $this->capacidad_total_m3,
            'horario' => $this->horario_atencion,
            'encargado' => $this->encargado,
            'zona' => $this->zona
        ];
    }

    // Para selectores
    public function toSelectArray(): array
    {
        return [
            'value' => $this->id,
            'label' => $this->nombre,
            'zona' => $this->zona['nombre'] ?? null
        ];
    }
}
