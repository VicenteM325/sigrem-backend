<?php

namespace App\Services\RecoleccionService;

use App\Models\rutas\Ruta;
use App\Models\rutas\AsignacionRutaCamion;
use Carbon\Carbon;

class GeneradorPuntosService
{
    // Factores por tipo de zona (basado en los tipos de la zona)
    const FACTORES_TIPO_ZONA = [
        'Residencial' => 1.0,
        'Comercial' => 1.5,
        'Industrial' => 2.0,
        'Mixta' => 1.3
    ];

    // Factores por día de la semana
    const FACTOR_DIA = [
        'Lunes' => 1.0,
        'Martes' => 0.95,
        'Miércoles' => 0.95,
        'Jueves' => 1.0,
        'Viernes' => 1.15,
        'Sábado' => 1.3,  // Sábado más basura
        'Domingo' => 1.2   // Domingo también más basura
    ];

    /**
     * Generar puntos de recolección para una asignación
     */
    public function generarPuntos(AsignacionRutaCamion $asignacion, int $idRecoleccion): array
    {
        $ruta = $asignacion->ruta;
        $zona = $ruta->zona;
        $fecha = Carbon::parse($asignacion->fecha_programada);
        $diaSemana = $this->getDiaSemanaEspanol($fecha->dayOfWeek);

        // Obtener tipos de zona (de la tabla tipos_zona)
        $tiposZona = $zona->tiposZona()->pluck('nombre_tipo_zona')->toArray();

        // Calcular número de puntos (entre 15-30)
        $numPuntos = $this->calcularNumeroPuntos(
            $tiposZona,
            $ruta->distancia_km
        );

        // Obtener puntos de la ruta
        $puntosRuta = $this->obtenerPuntosRuta($ruta);

        // Generar posiciones aleatorias a lo largo de la ruta
        $posiciones = $this->generarPosicionesAleatorias($numPuntos);

        // Generar puntos de recolección
        $puntos = [];
        $totalVolumen = 0;

        foreach ($posiciones as $indice => $posicion) {
            // Encontrar punto en la ruta
            $puntoGeo = $this->encontrarPuntoEnRuta($puntosRuta, $posicion);

            // Calcular volumen estimado (50-500 kg)
            $volumen = $this->calcularVolumenEstimado(
                $tiposZona,
                $diaSemana,
                $indice,
                $numPuntos
            );

            $totalVolumen += $volumen;

            $puntos[] = [
                'id_recoleccion' => $idRecoleccion,
                'latitud' => $puntoGeo['lat'],
                'longitud' => $puntoGeo['lng'],
                'volumen_estimado_kg' => $volumen,
                'estado_recoleccion' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        \Log::info('Recolección generada', [
            'asignacion_id' => $asignacion->id_asignacion,
            'zona' => $zona->nombre_zona,
            'tipos_zona' => $tiposZona,
            'dia_semana' => $diaSemana,
            'factor_dia' => self::FACTOR_DIA[$diaSemana],
            'puntos_generados' => $numPuntos,
            'total_volumen_kg' => $totalVolumen,
            'promedio_punto' => round($totalVolumen / $numPuntos, 2)
        ]);

        return $puntos;
    }

    /**
     * Calcular número de puntos (15-30) considerando tipo de zona y distancia
     */
    private function calcularNumeroPuntos(array $tiposZona, float $distancia): int
    {
        $minPuntos = 15;
        $maxPuntos = 30;

        // Factor por tipo de zona
        $factorTipo = $this->calcularFactorTipoZona($tiposZona);

        // Factor por distancia (rutas más largas = más puntos)
        $factorDistancia = min(1.2, max(0.8, $distancia / 8));

        // Cálculo: centro del rango (22.5) más variación según factores
        $rangoMedio = ($minPuntos + $maxPuntos) / 2; // 22.5
        $variacion = ($maxPuntos - $minPuntos) / 2;  // 7.5

        $puntos = round(($rangoMedio + ($variacion * ($factorTipo - 1))) * $factorDistancia);

        return max($minPuntos, min($maxPuntos, $puntos));
    }

    /**
     * Calcular factor combinado por tipos de zona
     */
    private function calcularFactorTipoZona(array $tiposZona): float
    {
        if (empty($tiposZona)) {
            return 1.0;
        }

        $factores = array_map(function($tipo) {
            return self::FACTORES_TIPO_ZONA[$tipo] ?? 1.0;
        }, $tiposZona);

        return array_sum($factores) / count($factores);
    }

    /**
     * Calcular volumen estimado (50-500 kg) considerando todos los factores
     */
    private function calcularVolumenEstimado(
        array $tiposZona,
        string $diaSemana,
        int $indice,
        int $totalPuntos
    ): float {
        $minVolumen = 50;
        $maxVolumen = 500;

        // Factor por tipo de zona
        $factorTipo = $this->calcularFactorTipoZona($tiposZona);

        // Factor por día de la semana
        $factorDia = self::FACTOR_DIA[$diaSemana] ?? 1.0;

        // Factor por posición (curva normal: más volumen en el centro de la ruta)
        $posicionRelativa = $indice / ($totalPuntos - 1);
        $factorPosicion = 1.0 + 0.3 * sin($posicionRelativa * M_PI); // 0.7 - 1.3

        // Volumen base aleatorio entre 50-500
        $volumenBase = $minVolumen + ($maxVolumen - $minVolumen) * (rand(0, 100) / 100);

        // Cálculo final
        $volumen = $volumenBase * $factorTipo * $factorDia * $factorPosicion;

        return round(max($minVolumen, min($maxVolumen, $volumen)), 2);
    }

    /**
     * Generar posiciones aleatorias a lo largo de la ruta (0 a 1)
     */
    private function generarPosicionesAleatorias(int $cantidad): array
    {
        $posiciones = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $posiciones[] = rand(0, 1000) / 1000; // Aleatorio entre 0 y 1
        }

        // Ordenar para mantener el orden de la ruta
        sort($posiciones);

        return $posiciones;
    }

    /**
     * Encontrar punto en la ruta basado en posición (0-1)
     */
    private function encontrarPuntoEnRuta(array $puntosRuta, float $posicion): array
    {
        $numPuntos = count($puntosRuta);

        if ($numPuntos === 1) {
            return $puntosRuta[0];
        }

        // Calcular qué segmento de la ruta corresponde
        $indiceSegmento = floor($posicion * ($numPuntos - 1));
        $indiceSegmento = min($indiceSegmento, $numPuntos - 2);

        $progreso = ($posicion * ($numPuntos - 1)) - $indiceSegmento;

        $p1 = $puntosRuta[$indiceSegmento];
        $p2 = $puntosRuta[$indiceSegmento + 1];

        // Interpolación lineal entre los dos puntos
        $lat = $p1['lat'] + ($p2['lat'] - $p1['lat']) * $progreso;
        $lng = $p1['lng'] + ($p2['lng'] - $p1['lng']) * $progreso;

        // Pequeña variación aleatoria (±5 metros) para que no queden exactamente en línea recta
        $lat += (rand(-15, 15) / 100000);
        $lng += (rand(-15, 15) / 100000);

        return [
            'lat' => round($lat, 8),
            'lng' => round($lng, 8)
        ];
    }

    /**
     * Obtener puntos de la ruta ordenados por su orden
     */
    private function obtenerPuntosRuta(Ruta $ruta): array
    {
        // Cargar la relación si no está cargada
        if (!$ruta->relationLoaded('puntosRuta')) {
            $ruta->load('puntosRuta');
        }

        $puntos = [];

        foreach ($ruta->puntosRuta as $punto) {
            $puntos[] = [
                'lat' => (float) $punto->latitud,
                'lng' => (float) $punto->longitud,
                'orden' => $punto->orden
            ];
        }

        // Seguimiento por orden
        usort($puntos, fn($a, $b) => $a['orden'] <=> $b['orden']);

        return $puntos;
    }

    /**
     * Obtener día de semana
     */
    private function getDiaSemanaEspanol(int $dayOfWeek): string
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $dias[$dayOfWeek] ?? 'Lunes';
    }
}
