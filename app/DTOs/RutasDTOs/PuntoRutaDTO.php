<?php

namespace App\DTOs\RutasDTOs;

class PuntoRutaDTO
{
    public function __construct(
        public readonly float $lat,
        public readonly float $lng,
        public readonly int $orden
    ) {}

    /**
     * Crear desde array (para request)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            lat: (float) $data['lat'],
            lng: (float) $data['lng'],
            orden: (int) ($data['orden'] ?? 0)
        );
    }

    /**
     * Crear desde modelo (para respuesta)
     */
    public static function fromModel($punto): self
    {
        return new self(
            lat: (float) $punto->latitud,
            lng: (float) $punto->longitud,
            orden: (int) $punto->orden
        );
    }

    /**
     * Convertir a array para guardar en BD
     */
    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'orden' => $this->orden
        ];
    }

    /**
     * Convertir a array para respuestas API
     */
    public function toResponseArray(): array
    {
        return [
            'latitud' => $this->lat,
            'longitud' => $this->lng,
            'orden' => $this->orden
        ];
    }
}