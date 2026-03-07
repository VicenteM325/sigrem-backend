<?php

namespace App\Repositories;

use App\Models\camiones\Camion;
use Illuminate\Database\Eloquent\Collection;

class CamionRepository extends BaseRepository
{
    public function __construct(Camion $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?Camion
    {
        return Camion::with(['conductor.user', 'asignaciones'])->find($id);
    }

    public function getAllWithConductor(): Collection
    {
        return Camion::with('conductor.user')->get();
    }

    public function findDisponibles(): Collection
    {
        return Camion::where('estado_vehiculo', 'operativo')
                     ->whereDoesntHave('asignacionesActivas')
                     ->with('conductor.user')
                     ->get();
    }

    public function findByPlaca(string $placa): ?Camion
    {
        return Camion::where('placa', $placa)->first();
    }

    public function getEnMantenimiento(): Collection
    {
        return Camion::where('estado_vehiculo', 'mantenimiento')
                     ->with('conductor.user')
                     ->get();
    }

    public function getCamionesSinConductor(): Collection
    {
        return Camion::whereNull('id_conductor')
                     ->where('estado_vehiculo', 'operativo')
                     ->get();
    }

    public function getCamionesPorConductor(int $idConductor): Collection
    {
        return Camion::where('id_conductor', $idConductor)
                     ->with('asignaciones')
                     ->get();
    }

    public function findDisponiblesParaFecha(string $fecha): Collection
    {
        return Camion::where('estado_vehiculo', 'operativo')
            ->whereDoesntHave('asignaciones', function($query) use ($fecha) {
                $query->whereDate('fecha_programada', $fecha)
                      ->whereIn('estado', ['programada', 'en_proceso']);
            })
            ->with('conductor.user')
            ->get();
    }

    public function getModel()
    {
        return $this->model;
    }
}