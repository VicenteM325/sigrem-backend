<?php

namespace App\DTOs\RutasDTOs;

class RutaDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre_ruta,
        public readonly ?string $descripcion,
        public readonly int $id_zona,
        public readonly int $id_estado_ruta,
        public readonly float $coordenada_inicio_lat,
        public readonly float $coordenada_inicio_lng,
        public readonly float $coordenada_fin_lat,
        public readonly float $coordenada_fin_lng,
        public readonly float $distancia_km,
        public readonly string $horario_inicio,
        public readonly string $horario_fin,
        public readonly array $puntos_intermedios = [],
        public readonly array $dias_recoleccion = [],
        public readonly array $tipos_residuo = []
    ) {}

    /**
     * Crear desde request (para crear/actualizar)
     */
    public static function fromRequest(array $data): self
    {
        // Procesar puntos intermedios si vienen
        $puntos = [];
        if (isset($data['puntos_intermedios']) && is_array($data['puntos_intermedios'])) {
            foreach ($data['puntos_intermedios'] as $punto) {
                $puntos[] = PuntoRutaDTO::fromArray($punto);
            }
        }

        return new self(
            id: $data['id'] ?? null,
            nombre_ruta: $data['nombre_ruta'],
            descripcion: $data['descripcion'] ?? null,
            id_zona: (int) $data['id_zona'],
            id_estado_ruta: (int) ($data['id_estado_ruta'] ?? 1), // 1 = Activa por defecto
            coordenada_inicio_lat: (float) $data['coordenada_inicio_lat'],
            coordenada_inicio_lng: (float) $data['coordenada_inicio_lng'],
            coordenada_fin_lat: (float) $data['coordenada_fin_lat'],
            coordenada_fin_lng: (float) $data['coordenada_fin_lng'],
            distancia_km: (float) $data['distancia_km'],
            horario_inicio: $data['horario_inicio'],
            horario_fin: $data['horario_fin'],
            puntos_intermedios: $puntos,
            dias_recoleccion: $data['dias_recoleccion'] ?? [],
            tipos_residuo: $data['tipos_residuo'] ?? []
        );
    }

    /**
     * Crear desde modelo (para respuestas)
     */
    public static function fromModel($ruta): self
    {
        $puntos = [];
        foreach ($ruta->puntosRuta as $punto) {
            $puntos[] = PuntoRutaDTO::fromModel($punto);
        }

        return new self(
            id: $ruta->id_ruta,
            nombre_ruta: $ruta->nombre_ruta,
            descripcion: $ruta->descripcion,
            id_zona: $ruta->id_zona,
            id_estado_ruta: $ruta->id_estado_ruta,
            coordenada_inicio_lat: $ruta->coordenada_inicio_lat,
            coordenada_inicio_lng: $ruta->coordenada_inicio_lng,
            coordenada_fin_lat: $ruta->coordenada_fin_lat,
            coordenada_fin_lng: $ruta->coordenada_fin_lng,
            distancia_km: $ruta->distancia_km,
            horario_inicio: $ruta->horario_inicio->format('H:i'),
            horario_fin: $ruta->horario_fin->format('H:i'),
            puntos_intermedios: $puntos,
            dias_recoleccion: $ruta->diasRecoleccion->pluck('nombre_dia')->toArray(),
            tipos_residuo: $ruta->tiposResiduo->pluck('id_tipo_residuo')->toArray()
        );
    }

    /**
     * Convertir a array para guardar en BD
     */
    public function toArray(): array
    {
        return [
            'nombre_ruta' => $this->nombre_ruta,
            'descripcion' => $this->descripcion,
            'id_zona' => $this->id_zona,
            'id_estado_ruta' => $this->id_estado_ruta,
            'coordenada_inicio_lat' => $this->coordenada_inicio_lat,
            'coordenada_inicio_lng' => $this->coordenada_inicio_lng,
            'coordenada_fin_lat' => $this->coordenada_fin_lat,
            'coordenada_fin_lng' => $this->coordenada_fin_lng,
            'distancia_km' => $this->distancia_km,
            'horario_inicio' => $this->horario_inicio,
            'horario_fin' => $this->horario_fin,
        ];
    }

    /**
     * Convertir a array para respuestas API completas
     */
    public function toResponseArray(): array
    {
        // Procesar puntos intermedios para respuesta
        $puntosResponse = [];
        foreach ($this->puntos_intermedios as $punto) {
            $puntosResponse[] = $punto->toResponseArray();
        }

        // Obtener nombres de días en español
        $diasMap = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
        ];

        $diasResponse = [];
        foreach ($this->dias_recoleccion as $dia) {
            $diasResponse[] = $diasMap[$dia] ?? $dia;
        }

        return [
            'id' => $this->id,
            'nombre' => $this->nombre_ruta,
            'descripcion' => $this->descripcion,
            'id_zona' => $this->id_zona,
            'estado' => [
                'id' => $this->id_estado_ruta,
                'nombre' => $this->getEstadoNombre()
            ],
            'coordenadas' => [
                'inicio' => [
                    'lat' => $this->coordenada_inicio_lat,
                    'lng' => $this->coordenada_inicio_lng
                ],
                'fin' => [
                    'lat' => $this->coordenada_fin_lat,
                    'lng' => $this->coordenada_fin_lng
                ]
            ],
            'distancia_km' => $this->distancia_km,
            'horario' => [
                'inicio' => $this->horario_inicio,
                'fin' => $this->horario_fin
            ],
            'puntos_intermedios' => $puntosResponse,
            'dias_recoleccion' => $diasResponse,
            'tipos_residuo' => $this->tipos_residuo
        ];
    }

    /**
     * Versión simplificada para listados
     */
    public function toListResponseArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_ruta,
            'zona_id' => $this->id_zona,
            'distancia' => $this->distancia_km,
            'horario' => $this->horario_inicio . ' - ' . $this->horario_fin,
            'estado_id' => $this->id_estado_ruta
        ];
    }

    private function getEstadoNombre(): string
    {
        return match($this->id_estado_ruta) {
            1 => 'Activa',
            2 => 'En mantenimiento',
            3 => 'Inactiva',
            default => 'Desconocido'
        };
    }

    /**
     * Validar que los datos son correctos
     */
    public function isValid(): bool
    {
        return !empty($this->nombre_ruta) 
            && $this->id_zona > 0
            && $this->distancia_km > 0
            && !empty($this->dias_recoleccion)
            && !empty($this->tipos_residuo);
    }
}