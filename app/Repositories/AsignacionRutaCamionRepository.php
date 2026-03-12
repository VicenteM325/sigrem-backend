<?php

namespace App\Repositories;

use App\Models\rutas\AsignacionRutaCamion;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AsignacionRutaCamionRepository extends BaseRepository
{
    public function __construct(AsignacionRutaCamion $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?AsignacionRutaCamion
    {
        return AsignacionRutaCamion::with(['ruta.zona', 'camion.conductor.user', 'recoleccion'])->find($id);
    }

    public function getAllWithRelations(array $filtros = []): Collection
    {
        $query = AsignacionRutaCamion::with(['ruta.zona', 'camion.conductor.user', 'recoleccion']);

        // Aplicar filtros
        if (isset($filtros['fecha'])) {
            $query->whereDate('fecha_programada', $filtros['fecha']);
        }

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $query->whereBetween('fecha_programada', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
        }

        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['id_camion'])) {
            $query->where('id_camion', $filtros['id_camion']);
        }

        if (isset($filtros['id_ruta'])) {
            $query->where('id_ruta', $filtros['id_ruta']);
        }

        return $query->orderBy('fecha_programada')->get();
    }

    public function getAsignacionesByFecha(string $fecha): Collection
    {
        return AsignacionRutaCamion::with(['ruta', 'camion.conductor.user'])
            ->whereDate('fecha_programada', $fecha)
            ->orderBy('id_ruta')
            ->get();
    }

    public function getAsignacionesByRangoFechas(string $fechaInicio, string $fechaFin): Collection
    {
        return AsignacionRutaCamion::with(['ruta', 'camion.conductor.user'])
            ->whereBetween('fecha_programada', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_programada')
            ->get();
    }

    public function getAsignacionesByCamion(int $idCamion): Collection
    {
        return AsignacionRutaCamion::with(['ruta'])
            ->where('id_camion', $idCamion)
            ->orderBy('fecha_programada', 'desc')
            ->get();
    }

    public function getAsignacionesByRuta(int $idRuta): Collection
    {
        return AsignacionRutaCamion::with(['camion.conductor.user'])
            ->where('id_ruta', $idRuta)
            ->orderBy('fecha_programada', 'desc')
            ->get();
    }

    public function getAsignacionesPendientes(): Collection
    {
        return AsignacionRutaCamion::with(['ruta', 'camion.conductor.user'])
            ->whereIn('estado', ['programada', 'en_proceso'])
            ->whereDate('fecha_programada', '>=', Carbon::today())
            ->orderBy('fecha_programada')
            ->get();
    }

    public function findAsignacionActivaByCamion(int $idCamion): ?AsignacionRutaCamion
    {
        return AsignacionRutaCamion::where('id_camion', $idCamion)
            ->whereIn('estado', ['programada', 'en_proceso'])
            ->whereDate('fecha_programada', '>=', Carbon::today())
            ->first();
    }

    public function findAsignacionActivaByRuta(int $idRuta, ?string $fecha = null): ?AsignacionRutaCamion
    {
        $query = AsignacionRutaCamion::where('id_ruta', $idRuta)
            ->whereIn('estado', ['programada', 'en_proceso']);

        if ($fecha) {
            $query->whereDate('fecha_programada', $fecha);
        } else {
            $query->whereDate('fecha_programada', '>=', Carbon::today());
        }

        return $query->first();
    }

    public function countAsignacionesByCamionAndFecha(int $idCamion, string $fecha): int
    {
        return AsignacionRutaCamion::where('id_camion', $idCamion)
            ->whereDate('fecha_programada', $fecha)
            ->whereIn('estado', ['programada', 'en_proceso'])
            ->count();
    }

    public function getEstadisticasPorRangoFechas(string $fechaInicio, string $fechaFin): array
    {
        $asignaciones = $this->getAsignacionesByRangoFechas($fechaInicio, $fechaFin);

        return [
            'total' => $asignaciones->count(),
            'por_estado' => [
                'programada' => $asignaciones->where('estado', 'programada')->count(),
                'en_proceso' => $asignaciones->where('estado', 'en_proceso')->count(),
                'completada' => $asignaciones->where('estado', 'completada')->count(),
                'cancelada' => $asignaciones->where('estado', 'cancelada')->count(),
            ],
            'total_estimado_kg' => $asignaciones->sum('total_estimado_kg'),
            'promedio_por_dia' => $asignaciones->count() / max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1)
        ];
    }
}
