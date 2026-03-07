<?php

namespace App\Repositories;

use App\Models\rutas\Ruta;
use App\Models\rutas\PuntoRuta;
use Illuminate\Database\Eloquent\Collection;

class RutaRepository extends BaseRepository
{
    public function __construct(Ruta $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtener todas las rutas con sus relaciones básicas
     */
    public function getAllWithRelations(): Collection
    {
        return Ruta::with([
            'estado', 
            'zona', 
            'puntosRuta' => fn($q) => $q->orderBy('orden'),
            'tiposResiduo', 
            'diasRecoleccion'
        ])->orderBy('nombre_ruta')->get();
    }

    /**
     * Buscar ruta por ID con todas las relaciones
     */
    public function findWithRelations(int $id): ?Ruta
    {
        return Ruta::with([
            'estado', 
            'zona', 
            'puntosRuta' => fn($q) => $q->orderBy('orden'),
            'tiposResiduo', 
            'diasRecoleccion'
        ])->find($id);
    }

    /**
     * Buscar ruta completa para edición
     */
    public function findRutaCompleta(int $id): ?Ruta
    {
        return Ruta::with([
            'estado',
            'zona',
            'puntosRuta' => fn($q) => $q->orderBy('orden'),
            'tiposResiduo',
            'diasRecoleccion',
            'asignaciones' => fn($q) => $q->with('camion.conductor.user')->latest()
        ])->find($id);
    }

    /**
     * Obtener rutas activas para el mapa
     */
    public function getRutasActivas(): Collection
    {
        return Ruta::with(['estado', 'zona', 'puntosRuta', 'diasRecoleccion'])
            ->whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))
            ->orderBy('nombre_ruta')
            ->get();
    }

    /**
     * Obtener rutas por zona
     */
    public function getByZona(int $zonaId): Collection
    {
        return Ruta::with(['estado', 'puntosRuta', 'diasRecoleccion'])
            ->where('id_zona', $zonaId)
            ->get();
    }

    /**
     * Guardar puntos intermedios de una ruta
     */
    public function savePuntosRuta(int $rutaId, array $puntos): void
    {
        // Eliminar puntos existentes
        PuntoRuta::where('id_ruta', $rutaId)->delete();
        
        // Crear nuevos puntos
        foreach ($puntos as $index => $punto) {
            PuntoRuta::create([
                'id_ruta' => $rutaId,
                'latitud' => $punto['lat'],
                'longitud' => $punto['lng'],
                'orden' => $index + 1
            ]);
        }
    }

    /**
     * Guardar días de recolección
     */
    public function saveDiasRecoleccion(Ruta $ruta, array $dias): void
    {
        $ruta->diasRecoleccion()->delete();
        
        foreach ($dias as $dia) {
            $ruta->diasRecoleccion()->create(['nombre_dia' => $dia]);
        }
    }

    /**
     * Sincronizar tipos de residuo
     */
    public function syncTiposResiduo(Ruta $ruta, array $tiposIds): void
    {
        $ruta->tiposResiduo()->sync($tiposIds);
    }

    /**
     * Buscar rutas por nombre (para autocomplete)
     */
    public function searchByNombre(string $termino): Collection
    {
        return Ruta::with(['estado', 'zona'])
            ->where('nombre_ruta', 'ILIKE', "%{$termino}%")
            ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
            ->limit(10)
            ->get();
    }

    /**
     * Obtener estadísticas básicas
     */
    public function getEstadisticas(): array
    {
        return [
            'total_rutas' => Ruta::count(),
            'rutas_activas' => Ruta::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))->count(),
            'total_km' => Ruta::sum('distancia_km'),
            'promedio_km' => Ruta::avg('distancia_km')
        ];
    }
}