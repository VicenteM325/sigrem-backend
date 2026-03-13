<?php

namespace App\Repositories;

use App\Models\recoleccion\Recoleccion;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class RecoleccionRepository extends BaseRepository
{
    public function __construct(Recoleccion $model)
    {
        parent::__construct($model);
    }

    private function aplicarFiltroPorUsuario($query, $user)
    {
        if ($user->hasRole('conductor')) {
            $query->whereHas('asignacion.camion.conductor.user', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        return $query;
    }

    public function findWithAsignacion(int $id, $user): ?Recoleccion
    {
        $query = Recoleccion::with([
            'asignacion.ruta',
            'asignacion.camion.conductor.user',
            'puntosBasura'
        ])->where('id_recoleccion', $id);

        $this->aplicarFiltroPorUsuario($query, $user);

        return $query->first();
    }

    public function getAllWithAsignacion($user): Collection
    {
        $query = Recoleccion::with([
            'asignacion.ruta',
            'asignacion.camion.conductor.user',
            'puntosBasura'
        ])->orderBy('created_at', 'desc');

        $this->aplicarFiltroPorUsuario($query, $user);

        return $query->get();
    }

    public function findByAsignacion(int $idAsignacion): ?Recoleccion
    {
        return Recoleccion::where('id_asignacion', $idAsignacion)->first();
    }

    public function findPendientes($user): Collection
    {
        $query = Recoleccion::where('estado_recoleccion', 'programada')
            ->whereHas('asignacion', function($query) {
                $query->where('fecha_programada', '>=', Carbon::today());
            })
            ->with(['asignacion.ruta', 'asignacion.camion.conductor.user'])
            ->orderBy('created_at', 'asc');

        $this->aplicarFiltroPorUsuario($query, $user);

        return $query->get();
    }

    public function findEnProceso($user): Collection
    {
        $query = Recoleccion::where('estado_recoleccion', 'en_proceso')
            ->with(['asignacion.ruta', 'asignacion.camion.conductor.user']);

        $this->aplicarFiltroPorUsuario($query, $user);

        return $query->get();
    }

    public function findCompletadasEnFecha(string $fecha): Collection
    {
        return Recoleccion::whereDate('hora_fin', $fecha)
            ->where('estado_recoleccion', 'completada')
            ->with(['asignacion.ruta', 'asignacion.camion'])
            ->get();
    }

    public function getEstadisticasPorFecha(string $fechaInicio, string $fechaFin): array
    {
        $recolecciones = Recoleccion::whereBetween('hora_fin', [$fechaInicio, $fechaFin])
            ->where('estado_recoleccion', 'completada')
            ->get();

        return [
            'total_recolecciones' => $recolecciones->count(),
            'total_basura' => $recolecciones->sum('basura_recolectada_ton'),
            'promedio_basura' => $recolecciones->avg('basura_recolectada_ton'),
            'tiempo_promedio' => $this->calcularTiempoPromedio($recolecciones)
        ];
    }

    private function calcularTiempoPromedio($recolecciones): ?float
    {
        $tiempos = [];
        foreach ($recolecciones as $rec) {
            if ($rec->hora_inicio && $rec->hora_fin) {
                $inicio = Carbon::parse($rec->hora_inicio);
                $fin = Carbon::parse($rec->hora_fin);
                $tiempos[] = $inicio->diffInMinutes($fin);
            }
        }

        return count($tiempos) > 0 ? array_sum($tiempos) / count($tiempos) : null;
    }
}
