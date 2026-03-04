<?php

namespace App\DTOs;

class ProfileDTO
{
    public function __construct(
        public readonly string $tipo,
        public readonly ?string $licencia = null,
        public readonly ?string $fecha_vencimiento_licencia = null,
        public readonly ?string $categoria_licencia = null,
        public readonly ?bool $disponible = null,
        public readonly ?int $puntos_acumulados = null,
        public readonly ?int $nivel = null,
        public readonly ?array $logros = null,
        public readonly ?array $preferencias = null
    ) {}

    public static function fromConductor($conductor): ?self
    {
        if (!$conductor) return null;
        
        return new self(
            tipo: 'conductor',
            licencia: $conductor->licencia,
            fecha_vencimiento_licencia: $conductor->fecha_vencimiento_licencia,
            categoria_licencia: $conductor->categoria_licencia,
            disponible: $conductor->disponible
        );
    }

    public static function fromCiudadano($ciudadano): ?self
    {
        if (!$ciudadano) return null;
        
        return new self(
            tipo: 'ciudadano',
            puntos_acumulados: $ciudadano->puntos_acumulados,
            nivel: $ciudadano->nivel,
            logros: $ciudadano->logros ? json_decode($ciudadano->logros, true) : null,
            preferencias: $ciudadano->preferencias ? json_decode($ciudadano->preferencias, true) : null
        );
    }

    // AGREGAR ESTE MÉTODO
    public function toArray(): array
    {
        $data = [
            'tipo' => $this->tipo,
        ];

        if ($this->tipo === 'conductor') {
            $data['licencia'] = $this->licencia;
            $data['fecha_vencimiento_licencia'] = $this->fecha_vencimiento_licencia;
            $data['categoria_licencia'] = $this->categoria_licencia;
            $data['disponible'] = $this->disponible;
        } else {
            $data['puntos_acumulados'] = $this->puntos_acumulados;
            $data['nivel'] = $this->nivel;
            $data['logros'] = $this->logros;
            $data['preferencias'] = $this->preferencias;
        }

        return $data;
    }
}