<?php

namespace App\Repositories;

use App\Models\contenedores\VaciadoContenedor;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class VaciadoContenedorRepository extends BaseRepository
{
    public function __construct(VaciadoContenedor $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?VaciadoContenedor
    {
        return VaciadoContenedor::with('contenedor.puntoVerde', 'contenedor.material')->find($id);
    }

    public function getPendientes(): Collection
    {
        return VaciadoContenedor::with('contenedor.puntoVerde', 'contenedor.material')
            ->where('estado', 'programado')
            ->where('fecha_programada', '>=', Carbon::today())
            ->orderBy('fecha_programada', 'asc')
            ->get();
    }

    public function getByFecha(string $fecha): Collection
    {
        return VaciadoContenedor::with('contenedor.puntoVerde', 'contenedor.material')
            ->whereDate('fecha_programada', $fecha)
            ->orderBy('fecha_programada', 'asc')
            ->get();
    }

    public function getByContenedor(int $idContenedor): Collection
    {
        return VaciadoContenedor::where('id_contenedor', $idContenedor)
            ->orderBy('fecha_programada', 'desc')
            ->get();
    }
}
