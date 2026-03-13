<?php

namespace App\Repositories;

use App\Models\contenedores\EntregaReciclaje;
use Illuminate\Database\Eloquent\Collection;

class EntregaReciclajeRepository extends BaseRepository
{
    public function __construct(EntregaReciclaje $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?EntregaReciclaje
    {
        return $this->model->with(['puntoVerde', 'material', 'ciudadano.user'])->find($id);
    }

    public function getAllWithRelations(): Collection
    {
        return $this->model->with(['puntoVerde', 'material', 'ciudadano.user'])
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function getByCiudadano(int $idCiudadano): Collection
    {
        return $this->model->with(['puntoVerde', 'material'])
            ->where('id_ciudadano', $idCiudadano)
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function getByPuntoVerde(int $idPuntoVerde): Collection
    {
        return $this->model->with(['material', 'ciudadano.user'])
            ->where('id_punto_verde', $idPuntoVerde)
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function getByFecha(string $fecha): Collection
    {
        return $this->model->with(['puntoVerde', 'material', 'ciudadano.user'])
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function getEstadisticasPorPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $entregas = $this->model->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->with('material')
            ->get();

        $totalKg = $entregas->sum('cantidad_kg');
        $totalEntregas = $entregas->count();

        $porMaterial = $entregas->groupBy('id_material')
            ->map(fn($items) => [
                'total_kg' => $items->sum('cantidad_kg'),
                'cantidad' => $items->count(),
                'material' => $items->first()->material->nombre_material ?? 'Desconocido'
            ])->values();

        return [
            'total_entregas' => $totalEntregas,
            'total_kg' => $totalKg,
            'promedio_kg' => $totalEntregas > 0 ? round($totalKg / $totalEntregas, 2) : 0,
            'por_material' => $porMaterial
        ];
    }
}
